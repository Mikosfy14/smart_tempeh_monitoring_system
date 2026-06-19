#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <WiFiClientSecure.h>

// ==========================================
// 1. KONFIGURASI JARINGAN & API SERVER
// ==========================================
const char* ssid       = "shafiq";
const char* password   = "shafiq140905";
const char* serverUrl  = "https://rizhomatix.web.id/api/telemetry";
const char* statusUrl  = "https://rizhomatix.web.id/api/device/status";
const char* apiKey     = "SmArTTeMPehSystem4891231!!?!";

const String deviceIdReal = "ESP32-001";  // Sensor fisik (data real)
const String deviceIdSim  = "ESP32-002";  // Data simulasi (dari script PHP)

// ==========================================
// 2. KONFIGURASI PIN HARDWARE
// ==========================================
#define DHTPIN       32
#define DHTTYPE      DHT22
#define DS18B20_PIN  25
#define MQ135_PIN    36
#define FAN_RELAY_PIN 21

// Inisialisasi Objek Sensor
DHT dht(DHTPIN, DHTTYPE);
OneWire oneWire(DS18B20_PIN);
DallasTemperature ds18b20(&oneWire);

// ==========================================
// 3. KALIBRASI SENSOR MQ-135
// ==========================================
const float R_O = 5450.0;   // Resistansi sensor di udara bersih (hasil kalibrasi)
const float R_L = 10000.0;  // Resistansi beban (load resistor pada modul MQ-135)
const float VC  = 5.0;      // Supply voltage modul MQ-135

// ==========================================
// 4. INTERVAL
// ==========================================
unsigned long timerDelay     = 5000;   // Kirim data setiap 5 detik (sesuai chart refresh)
unsigned long fanPollDelay   = 1000;   // Poll fan status setiap 1 detik
unsigned long lastSendTime   = 0;
unsigned long lastFanPollTime = 0;
bool fanIsOn = false;

// ==========================================
// 5. FUNGSI BACA SENSOR REAL
// ==========================================

float readInternalTemp() {
  ds18b20.requestTemperatures();
  float t = ds18b20.getTempCByIndex(0);
  if (t == DEVICE_DISCONNECTED_C || t < -50) {
    Serial.println("[SENSOR] DS18B20 gagal baca!");
    return 0.0;
  }
  return t;
}

float readRoomTemp() {
  float t = dht.readTemperature();
  if (isnan(t)) {
    Serial.println("[SENSOR] DHT22 gagal baca suhu!");
    return 0.0;
  }
  return t;
}

float readHumidity() {
  float h = dht.readHumidity();
  if (isnan(h)) {
    Serial.println("[SENSOR] DHT22 gagal baca kelembapan!");
    return 0.0;
  }
  return h;
}

float readAmonia() {
  int raw = analogRead(MQ135_PIN);

  // Konversi ADC ke tegangan output sensor
  float vOut = (raw / 4095.0) * 3.3;

  // Hindari division by zero
  if (vOut < 0.01) return 0.0;

  // Hitung resistansi sensor (Rs)
  float rs = R_L * (VC / vOut - 1.0);

  // Rasio Rs/Ro
  float ratio = rs / R_O;

  // Konversi ke PPM menggunakan kurva NH3 dari datasheet MQ-135
  float ppm = 116.6020682 * pow(ratio, -2.769034857);

  // Batasi rentang wajar
  if (ppm < 0) ppm = 0;
  if (ppm > 500) ppm = 500;

  return ppm;
}

// ==========================================
// 6. FUNGSI KIRIM DATA REAL KE SERVER
// ==========================================

bool sendTelemetry(float internalTemp, float amoniaLevel,
                   float roomTemp, float humidity, String &response) {
  StaticJsonDocument<200> jsonDoc;
  jsonDoc["device_id"]     = deviceIdReal;
  jsonDoc["internal_temp"] = serializedJsonString(String(internalTemp, 1));
  jsonDoc["amonia_level"]  = serializedJsonString(String(amoniaLevel, 1));
  jsonDoc["room_temp"]     = serializedJsonString(String(roomTemp, 1));
  jsonDoc["humidity"]      = serializedJsonString(String(humidity, 1));

  String requestBody;
  serializeJson(jsonDoc, requestBody);

  WiFiClientSecure client;
  client.setInsecure();

  HTTPClient http;
  http.begin(client, serverUrl);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-Key", apiKey);
  http.setTimeout(5000);

  int httpCode = http.POST(requestBody);

  if (httpCode > 0) {
    response = http.getString();
    http.end();
    return (httpCode == 200);
  }

  Serial.printf("[HTTP] Error code: %d\n", httpCode);
  http.end();
  return false;
}

// ==========================================
// 7. FUNGSI POLLING FAN STATUS (DUAL DEVICE)
// ==========================================

/**
 * Poll fan status dari satu device.
 * Return: "ON", "OFF", atau "" jika gagal.
 */
String pollFanStatus(String deviceId) {
  if (WiFi.status() != WL_CONNECTED) return "";

  String url = String(statusUrl) + "?device_id=" + deviceId;

  WiFiClientSecure client;
  client.setInsecure();

  HTTPClient http;
  http.begin(client, url);
  http.addHeader("X-API-Key", apiKey);
  http.addHeader("Accept", "application/json");
  http.setTimeout(3000);

  int httpCode = http.GET();

  if (httpCode == 200) {
    String payload = http.getString();

    StaticJsonDocument<512> doc;
    DeserializationError error = deserializeJson(doc, payload);

    if (!error) {
      const char* status = doc["status"];
      if (String(status) == "success") {
        String fan = doc["fan_status"] | "OFF";
        http.end();
        return fan;
      }
    }
  }

  http.end();
  return "";
}

/**
 * Poll fan status dari KEDUA device (ESP32-001 dan ESP32-002).
 * Kipas ON jika SALAH SATU device bilang ON (OR logic).
 *
 * Kenapa OR? Karena ini sistem mitigasi keselamatan.
 * Lebih baik kipas nyala karena satu device deteksi bahaya,
 * daripada mati karena device lain bilang aman.
 */
void updateFanFromServer() {
  String fan1 = pollFanStatus(deviceIdReal);
  String fan2 = pollFanStatus(deviceIdSim);

  // OR logic: ON jika salah satu bilang ON
  bool shouldBeOn = (fan1 == "ON" || fan2 == "ON");

  if (shouldBeOn != fanIsOn) {
    fanIsOn = shouldBeOn;

    if (fanIsOn) {
      digitalWrite(FAN_RELAY_PIN, LOW);
      Serial.println("[FAN] Kipas MENYALA - perintah dari server");
    } else {
      digitalWrite(FAN_RELAY_PIN, HIGH);
      Serial.println("[FAN] Kipas MATI - perintah dari server");
    }
  }

  // Debug: tampilkan status kedua device
  Serial.printf("[FAN] ESP32-001: %s | ESP32-002: %s | Kipas: %s\n",
                fan1.length() ? fan1.c_str() : "N/A",
                fan2.length() ? fan2.c_str() : "N/A",
                fanIsOn ? "ON" : "OFF");
}

// ==========================================
// 8. SETUP
// ==========================================

void setup() {
  Serial.begin(115200);
  Serial.println("\n============================================");
  Serial.println("  Mikosfy - ESP32 Smart Tempeh Monitoring");
  Serial.println("  Mode: Real Sensor + Dual Fan Polling");
  Serial.println("============================================\n");

  // Setup Pin Kipas (Active LOW relay)
  pinMode(FAN_RELAY_PIN, OUTPUT);
  digitalWrite(FAN_RELAY_PIN, HIGH);

  // Mulai Sensor Fisik
  dht.begin();
  ds18b20.begin();

  // Koneksi WiFi
  Serial.printf("[WIFI] Menghubungkan ke %s", ssid);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.printf("\n[WIFI] Terhubung! IP: %s\n\n", WiFi.localIP().toString().c_str());

  Serial.println("[INIT] Device Real  : " + deviceIdReal);
  Serial.println("[INIT] Device Sim   : " + deviceIdSim + " (data dari script PHP)");
  Serial.printf("[INIT] MQ-135 R_O   : %.1f ohm\n\n", R_O);
  Serial.println("[INIT] Fan Polling  : dual device (OR logic)");
  Serial.println("[INIT] Sensor Kirim : setiap 5 detik");
  Serial.println("[INIT] Fan Poll     : setiap 1 detik\n");
}

// ==========================================
// 9. LOOP
// ==========================================

void loop() {
  unsigned long now = millis();

  // Cek koneksi WiFi
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WIFI] Koneksi terputus! Reconnecting...");
    WiFi.begin(ssid, password);
    delay(3000);
    return;
  }

  // ------------------------------------------
  // A. POLL FAN STATUS SETIAP 1 DETIK
  // ------------------------------------------
  if (now - lastFanPollTime >= fanPollDelay) {
    lastFanPollTime = now;
    updateFanFromServer();
  }

  // ------------------------------------------
  // B. KIRIM DATA SENSOR SETIAP 10 DETIK
  // ------------------------------------------
  if (now - lastSendTime >= timerDelay) {
    lastSendTime = now;

    float roomTemp  = readRoomTemp();
    float humidity  = readHumidity();
    float intTemp   = readInternalTemp();
    float amonia    = readAmonia();

    Serial.println("---------------------------------------------");
    Serial.printf("[DATA] Suhu Tempe: %.1fC | Amonia: %.1f ppm | Ruang: %.1fC | Hum: %.1f%%\n",
                  intTemp, amonia, roomTemp, humidity);

    String response;
    bool ok = sendTelemetry(intTemp, amonia, roomTemp, humidity, response);

    if (ok) {
      Serial.println("[DATA] OK - Data terkirim sebagai " + deviceIdReal);

      // Eksekusi perintah kipas dari response telemetry
      StaticJsonDocument<512> respDoc;
      deserializeJson(respDoc, response);

      String fanStatus = respDoc["fan_status"] | "OFF";
      if (fanStatus == "ON") {
        digitalWrite(FAN_RELAY_PIN, LOW);
        fanIsOn = true;
        Serial.println("[FAN] Kipas MENYALA - perintah dari telemetry");
      } else {
        // Jangan matikan kipas di sini karena polling dual-device
        // yang mengontrol. Biarkan updateFanFromServer() yang handle.
      }
    } else {
      Serial.println("[DATA] FAIL - Gagal mengirim data.");
    }

    Serial.println("=============================================\n");
  }
}
