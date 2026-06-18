#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <WiFiClientSecure.h>

// ==========================================
// 1. KONFIGURASI JARINGAN & API SERVER
// ==========================================
const char* ssid     = "shafiq";
const char* password = "shafiq140905";
const char* serverUrl = "https://rizhomatix.web.id/api/device/status";
const char* apiKey    = "SmArTTeMPehSystem4891231!!?!";
const String deviceId = "ESP32-001";

// ==========================================
// 2. KONFIGURASI PIN HARDWARE
// ==========================================
#define FAN_RELAY_PIN 21   // Pin Relay untuk Kipas Mitigasi
#define LED_PIN        2   // LED onboard ESP32 (indikator status)

// ==========================================
// 3. INTERVAL POLLING
// ==========================================
const unsigned long POLL_INTERVAL = 1000;  // 1 detik

// ==========================================
// 4. GLOBAL VARIABLES
// ==========================================
unsigned long lastPollTime = 0;
bool fanIsOn = false;
bool serverConnected = false;

// ==========================================
//  SETUP
// ==========================================
void setup() {
    Serial.begin(115200);
    Serial.println("\n================================");
    Serial.println("Mikosfy — ESP32 Fan Controller");
    Serial.println("Mode: Polling Only (tanpa sensor)");
    Serial.println("================================\n");

    // Setup Pin Relay (Active LOW)
    pinMode(FAN_RELAY_PIN, OUTPUT);
    digitalWrite(FAN_RELAY_PIN, HIGH);  // HIGH = relay OFF

    // Setup LED indikator
    pinMode(LED_PIN, OUTPUT);
    digitalWrite(LED_PIN, LOW);

    // Koneksi WiFi
    connectWiFi();

    Serial.println("[INIT] Siap! Mulai polling server...\n");
}

// ==========================================
//  LOOP
// ==========================================
void loop() {
    // Cek koneksi WiFi
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("[WIFI] Koneksi terputus! Reconnecting...");
        connectWiFi();
    }

    // Polling server setiap 1 detik
    unsigned long now = millis();
    if (now - lastPollTime >= POLL_INTERVAL) {
        lastPollTime = now;
        pollFanStatus();
    }

    // LED indikator: kedip pelan saat terhubung
    if (serverConnected) {
        digitalWrite(LED_PIN, (millis() / 1000) % 2);
    } else {
        // Kedip cepat saat tidak terhubung ke server
        digitalWrite(LED_PIN, (millis() / 200) % 2);
    }
}

// ==========================================
//  WIFI CONNECTION
// ==========================================
void connectWiFi() {
    Serial.printf("[WIFI] Menghubungkan ke %s", ssid);
    WiFi.begin(ssid, password);

    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 30) {
        delay(500);
        Serial.print(".");
        attempts++;
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.printf("\n[WIFI] Terhubung! IP: %s\n\n", WiFi.localIP().toString().c_str());
    } else {
        Serial.println("\n[WIFI] Gagal terhubung! Akan dicoba lagi...\n");
    }
}

// ==========================================
//  POLLING FAN STATUS DARI SERVER
// ==========================================
void pollFanStatus() {
    if (WiFi.status() != WL_CONNECTED) {
        serverConnected = false;
        return;
    }

    // Bangun URL: GET /api/device/status?device_id=ESP32-001
    String url = String(serverUrl) + "?device_id=" + deviceId;

    WiFiClientSecure client;
    client.setInsecure();  // Skip SSL verification

    HTTPClient http;
    http.begin(client, url);
    http.addHeader("X-API-Key", apiKey);
    http.addHeader("Accept", "application/json");
    http.setTimeout(5000);

    int httpCode = http.GET();

    if (httpCode == 200) {
        serverConnected = true;
        String payload = http.getString();

        // Parse JSON response
        StaticJsonDocument<512> doc;
        DeserializationError error = deserializeJson(doc, payload);

        if (!error) {
            const char* status = doc["status"];
            const char* fan    = doc["fan_status"];

            if (String(status) == "success") {
                bool shouldBeOn = (String(fan) == "ON");

                // Cek perubahan status
                if (shouldBeOn != fanIsOn) {
                    fanIsOn = shouldBeOn;

                    if (fanIsOn) {
                        digitalWrite(FAN_RELAY_PIN, LOW);   // LOW = relay ON
                        Serial.println("[KIPAS] ✅ MENYALA — Perintah dari server");
                    } else {
                        digitalWrite(FAN_RELAY_PIN, HIGH);  // HIGH = relay OFF
                        Serial.println("[KIPAS] ❌ MATI — Perintah dari server");
                    }
                }
            }
        } else {
            Serial.printf("[ERROR] JSON parse gagal: %s\n", error.c_str());
        }
    } else if (httpCode > 0) {
        Serial.printf("[HTTP] Response code: %d\n", httpCode);
        serverConnected = false;
    } else {
        // Connection failed
        serverConnected = false;
    }

    http.end();
}
