# 🌱 Rizhomatix 
**IoT-Based Tempe Fermentation Monitoring & Mitigation System**

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Node.js](https://img.shields.io/badge/Node.js-43853D?style=for-the-badge&logo=node.js&logoColor=white)
![ESP32](https://img.shields.io/badge/ESP32-000000?style=for-the-badge&logo=espressif&logoColor=white)
![Ubuntu](https://img.shields.io/badge/Ubuntu-E95420?style=for-the-badge&logo=ubuntu&logoColor=white)

## 💻 Programming Languages
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![C++](https://img.shields.io/badge/C%2B%2B-00599C?style=for-the-badge&logo=c%2B%2B&logoColor=white)

## 🌐 Project Links
[![Website](https://img.shields.io/badge/Website_Dashboard-Rizhomatix-blue?style=for-the-badge&logo=google-chrome&logoColor=white)](https://rizhomatix.web.id/)
[![YouTube](https://img.shields.io/badge/Demo_Video-Watch-red?style=for-the-badge&logo=youtube&logoColor=white)](https://youtu.be/t_RRnhH6zRs)
[![Drive](https://img.shields.io/badge/Project_Report-Google_Drive-emerald?style=for-the-badge&logo=google-drive&logoColor=white)](https://drive.google.com/file/d/1jbaLcjBhZlkBGzMOGSHs8R7WlZsWTxQP/view?usp=sharing)


## 📖 Project Description
**Rizhomatix** is designed to solve fermentation process failures and financial losses faced by micro, small, and medium enterprises (MSMEs) in the tempe production industry due to soybean protein rotting triggered by exothermic extreme heat in traditional fermentation rooms.

This system utilizes an ESP32 microcontroller connected to a sensor array (DS18B20, DHT22, and MQ-135) to autonomously read parameters such as internal temperature, room humidity, and ammonia gas spikes. The sensor telemetry is sent via encrypted HTTP POST JSON requests to a Laravel backend hosted on a VPS running Ubuntu 24.04 LTS to be visualized on a web-based dashboard in real-time. If any threshold anomaly is detected, the server immediately triggers a relay module to activate a 12V DC cooling fan, while simultaneously hitting a local Node.js-based WhatsApp Gateway API to dispatch real-time emergency alert messages to the user's phone. The core benefit of this project is to modernize traditional labor methods through 24/7 autonomous monitoring, reduce product rejection rates, and maximize operational cost efficiency.

## ✨ Key Features
- **Real-time Telemetry:** Dynamic monitoring of temperature, relative humidity, and ammonia gas concentration levels.
- **Auto-Mitigation:** Automated actuation of the exhaust cooling fan via relay when the temperature reaches critical thresholds (>33°C).
- **Self-Hosted WhatsApp Gateway:** Instant emergency push notifications over WhatsApp using a standalone microservice, eliminating third-party paid API subscription dependencies.
- **Smart Dashboard:** A responsive web interface for device management (Device ID whitelist claiming), historical charts, and fermentation batch maturity curve analytics.
- **Reporting System:** Automated data logging and generation of fermentation reports exportable in PDF format.

## 🛠️ Technology Architecture

### Hardware Components
- **ESP32** (Main Microcontroller Core)
- **DS18B20** (Soybean Core / Liquid Temperature Sensor)
- **DHT22** (Incubator Ambient Temperature & Humidity Sensor)
- **MQ-135** (Air Quality / Ammonia Gas Detection Sensor)
- **1-Channel Relay Module** (Actuator Power Switch)
- **12V DC Fan** (Active Mitigation Cooling System)

### Software & Cloud Stack
- **Backend & Web:** Laravel 11 (PHP)
- **Database:** MySQL
- **Frontend Styling:** Tailwind CSS v4.0
- **WhatsApp Gateway Microservice:** Node.js, Express,     `whatsapp-web.js`, Puppeteer (Headless Browser with X11 backend)
- **Server Deployment:** VPS Linux Ubuntu 24.04 LTS
- **Process Manager:** PM2 (ensuring the Node.js service remains active continuously in the background)

## ⚙️ System Workflow

1. **Sensor Reading:** The ESP32 gathers physics parameters from the connected DS18B20, DHT22, and MQ-135 sensors.
2. **API Transmission:** The ESP32 structures the telemetry payload into JSON format and pushes it to the Laravel REST API endpoint `/api/telemetry` secured with a static API key header validation.
3. **Backend Processing:** The Laravel core validates the inbound request, stores log records into the database, executes threshold evaluations, updates device uptime states, and runs batch fermentation analytics.
4. **Dashboard Visualization:** The web interface dynamically polls data from database records using internal dashboard endpoints like `/api/dashboard/live` and `/api/dashboard/chart`.
5. **Notification Trigger:** In the event of an abnormal environmental event (e.g., high ammonia gas concentration or critical heat), Laravel sends an internal local HTTP POST request to the local port 3000 inside the VPS.
6. **Gateway Execution:** The Express server running on Node.js catches the incoming internal trigger under the continuous supervision of the PM2 process manager.
7. **Virtual Rendering:** The gateway boots a *Headless Browser Puppeteer* instance utilizing X11 graphics dependencies to map out the WhatsApp Web session page virtually.
8. **Message Delivery:** The backend script automatically constructs formatted text alerts and dispatches the warning directly to the device owner's registered WhatsApp phone number pulled dynamically from the database rows.
9. **Actuator Feedback Loop:** Concurrently, the ESP32 performs routine interval *polling* requests toward the `/api/device/status` endpoint to pull the latest fan command from the server, switching the physical relay on or off locally to mitigate extreme heat.


### 📷 System Architecture

<img width="1481" height="687" alt="arsitektur-rizhomatix" src="https://github.com/user-attachments/assets/1ad63316-add0-4a2e-ba44-7b53952a6812" />


### 📷 System Data Workflow

<img width="1411" height="767" alt="alur-data-integrasi-rizhomatix" src="https://github.com/user-attachments/assets/538cd260-b054-4429-9dfd-f1aed614283f" />


## 🚀 Future Development Plans
To transition the Rizhomatix ecosystem from a prototype into a production-grade enterprise commercial product, the following advancements are planned for future releases:

### 1. Embedded Smart Provisioning (Zero-Hardcode Configuration)
* **Dynamic Wi-Fi Provisioning:** Integrate a local captive portal system (e.g., using `WiFiManager` library in C++) inside the ESP32 core firmware. This completely eliminates hardcoded Wi-Fi credentials (`SSID` and `Password`) from the source code. If no local network connection is detected, the device will spin up an autonomous Access Point (AP), allowing users to configure Wi-Fi credentials via a local mobile browser interface.
* **Dynamic Hardware Registration (Smart Claiming):** Migrate from the manual database Device ID whitelisting to a hardware-generated identity protocol using the chip's unique **Hardware MAC Address**. Once connected to the cloud server via an endpoint handshake, the device is flagged as "Unclaimed." Users can securely claim ownership via the web dashboard through local network matching or temporary dynamic PIN pairing, providing a seamless plug-and-play onboarding user experience.

### 2. Machine Learning Deployment at the Edge & Cloud
* **Cloud-Based Anomaly Detection Models:** Upgrade the rule-based Expert System script into an advanced Machine Learning prediction pipeline (e.g., utilizing isolation forests or recurrent neural networks). This system will continuously analyze long-term telemetry multivariate trends (temperature, humidity, ammonia curves) to forecast precisely the exact minute of peak tempeh maturity, drastically mitigating human error.
* **TinyML Integration:** Explore the integration of low-power Edge AI inference scripts directly inside the ESP32 microcontroller, enabling autonomous ambient pattern decisions even during server or network connection downtime intervals.




Information Technology Study Program, Faculty of Vocational Studies, Universitas Brawijaya (2026).
