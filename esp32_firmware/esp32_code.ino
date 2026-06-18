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
const char* ssid = "shafiq";        
const char* password = "shafiq140905";
const char* serverUrl = "https://rizhomatix.web.id/api/telemetry"; 
const char* apiKey = "SmArTTeMPehSystem4891231!!?!";    
const String deviceId = "ESP32-001";    

// ==========================================
// 2. KONFIGURASI PIN HARDWARE
// ==========================================
#define DHTPIN 32           
#define DHTTYPE DHT22
#define DS18B20_PIN 25     
#define MQ135_PIN 36     
#define FAN_RELAY_PIN 21   

// Inisialisasi Objek Sensor
DHT dht(DHTPIN, DHTTYPE);
OneWire oneWire(DS18B20_PIN);
DallasTemperature ds18b20(&oneWire);

unsigned long lastTime = 0;
unsigned long timerDelay = 10000; 

void setup() {
  Serial.begin(115200);

  // Setup Pin Kipas
  pinMode(FAN_RELAY_PIN, OUTPUT);
  digitalWrite(FAN_RELAY_PIN, HIGH);

  // Mulai Sensor
  dht.begin();
  ds18b20.begin();

  // Koneksi WiFi
  WiFi.begin(ssid, password);
  Serial.print("Menghubungkan ke WiFi");
  while(WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Terhubung dengan sukses!");
}

void loop() {
  if ((millis() - lastTime) > timerDelay) {
    if(WiFi.status() == WL_CONNECTED){
      
      // ----------------------------------------
      // A. BACA DATA SENSOR
      // ----------------------------------------
      float roomTemp = dht.readTemperature();
      float humidity = dht.readHumidity();

      ds18b20.requestTemperatures();
      float internalTemp = ds18b20.getTempCByIndex(0);

      // (Opsional) Mapping nilai raw analog MQ135 ke estimasi PPM
      int amoniaRaw = analogRead(MQ135_PIN);
      float amoniaLevel = map(amoniaRaw, 0, 4095, 0, 500); 

      // Validasi DHT22
      if (isnan(roomTemp) || isnan(humidity)) {
        Serial.println("Peringatan: Gagal membaca DHT22!");
        roomTemp = 0; humidity = 0;
      }

      Serial.printf("Mengirim Data -> Suhu Ruang: %.1f | Kelembapan: %.1f | Suhu Tempe: %.1f | Amonia: %.1f\n", roomTemp, humidity, internalTemp, amoniaLevel);

      // ----------------------------------------
      // B. SUSUN DATA JSON
      // ----------------------------------------
      StaticJsonDocument<200> jsonDoc;
      jsonDoc["device_id"] = deviceId;
      jsonDoc["internal_temp"] = internalTemp;
      jsonDoc["amonia_level"] = amoniaLevel;
      jsonDoc["room_temp"] = roomTemp;
      jsonDoc["humidity"] = humidity;

      String requestBody;
      serializeJson(jsonDoc, requestBody);

      // ----------------------------------------
      // C. KIRIM KE SERVER WEB (API)
      // ----------------------------------------
      WiFiClientSecure client;
      client.setInsecure(); 

      HTTPClient http;
      http.begin(client, serverUrl); 
      http.addHeader("Content-Type", "application/json");
      http.addHeader("X-API-Key", apiKey); 

      int httpResponseCode = http.POST(requestBody);

      if (httpResponseCode > 0) {
        String response = http.getString();
        
        // ----------------------------------------
        // D. BACA PERINTAH KIPAS DARI SERVER
        // ----------------------------------------
        StaticJsonDocument<512> responseDoc;
        deserializeJson(responseDoc, response);

        String fanStatus = responseDoc["fan_status"]; // Akan bernilai "ON" atau "OFF"
        
        if(fanStatus == "ON") {
          digitalWrite(FAN_RELAY_PIN, LOW);  // Sinyal LOW menyalakan relay
          Serial.println("[EKSEKUSI] Kipas Mitigasi MENYALA!");
        } else {
          digitalWrite(FAN_RELAY_PIN, HIGH); // Sinyal HIGH mematikan relay
          Serial.println("[EKSEKUSI] Kipas Mitigasi MATI.");
        }
      } else {
        Serial.print("Error API. Kode HTTP: ");
        Serial.println(httpResponseCode);
      }
      http.end();
    } else {
      Serial.println("Koneksi WiFi Terputus!");
    }
    lastTime = millis();
  }
}