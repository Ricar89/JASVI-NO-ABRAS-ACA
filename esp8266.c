#include <ArduinoJson.h>
#include <ArduinoJson.hpp>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecureBearSSL.h>
#include <Servo.h>
#include <DHT.h>

const char* ssid = "****";
const char* password = "****";

const char* serverName = "https://richardatomiccompany.000webhostapp.com/control_status.php";

#define D4 16 
#define D5 17 
#define D6 18 
#define D7 19 

Servo servo1, servo2, servo3;
DHT dht(D4, DHT22);

const int servo1Pin = D5;
const int servo2Pin = D6;
const int servo3Pin = D7;

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }

  Serial.println("Connected to WiFi");

  servo1.attach(servo1Pin);
  servo2.attach(servo2Pin);
  servo3.attach(servo3Pin);
  dht.begin();
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    std::unique_ptr<BearSSL::WiFiClientSecure> client(new BearSSL::WiFiClientSecure);
    client->setInsecure(); 
    HTTPClient http;
    http.begin(*client, serverName);

    int httpResponseCode = http.GET();

    if (httpResponseCode > 0) {
      String payload = http.getString();
      Serial.println(payload);

      StaticJsonDocument<200> doc;
      DeserializationError error = deserializeJson(doc, payload);

      if (error) {
        Serial.print("Error deserializing JSON: ");
        Serial.println(error.c_str());
        return;
      }

      bool servo1Left = doc["servo1_left"] == "1";
      bool servo1Right = doc["servo1_right"] == "1";

      if (servo1Left) {
        servo1.write(45);
        Serial.println("Servo1 girando a la izquierda");
      } else if (servo1Right) {
        servo1.write(135);
        Serial.println("Servo1 girando a la derecha");
      } else {
        servo1.write(90);
        Serial.println("Servo1 en línea recta");
      }

      bool servo2Up = doc["servo2_up"] == "1";
      bool servo2Down = doc["servo2_down"] == "1";

      if (servo2Up) {
        servo2.write(135);
        Serial.println("Servo2 moviéndose hacia arriba");
      } else if (servo2Down) {
        servo2.write(45);
        Serial.println("Servo2 moviéndose hacia abajo");
      } else {
        servo2.write(90);
        Serial.println("Servo2 en posición neutral");
      }

      bool servo3Open = doc["servo3_open"] == "1";
      bool servo3Close = doc["servo3_close"] == "1";

      if (servo3Open) {
        servo3.write(135);
        Serial.println("Servo3 abriendo");
      } else if (servo3Close) {
        servo3.write(45);
        Serial.println("Servo3 cerrando");
      } else {
        servo3.write(90);
        Serial.println("Servo3 en posición neutral");
      }

      float temperature = dht.readTemperature();
      float humidity = dht.readHumidity();

      if (!isnan(temperature) && !isnan(humidity)) {
        String postData = "temperature=" + String(temperature) + "&humidity=" + String(humidity);

        http.begin(*client, serverName);
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        httpResponseCode = http.POST(postData);

        if (httpResponseCode > 0) {
          String response = http.getString();
          Serial.println(response);
        } else {
          Serial.print("Error on HTTP POST: ");
          Serial.println(httpResponseCode);
        }

        http.end();
      }
    } else {
      Serial.print("Error on HTTP request: ");
      Serial.println(httpResponseCode);
    }

    http.end();
  }

  delay(1000);
}