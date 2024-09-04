#include <ArduinoJson.h>
#include <ArduinoJson.hpp>
#include <ESP32Servo.h>
#include <WiFi.h>
#include <HTTPClient.h>

const char* ssid = "Estudiantes";
const char* password = "educar_2018";

const char* serverName = "https://richardatomiccompany.000webhostapp.com/control_status.php";

Servo servo0;

const int motorPin1 = 21; 
const int motorPin11 = 22;
const int motorPin2 = 5; 
const int motorPin22 = 18;
const int servoPin = 33; 
const int enableA = 23;
const int enableB = 19;

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }

  Serial.println("Connected to WiFi");
  
  pinMode(motorPin1, OUTPUT);
  pinMode(motorPin11, OUTPUT);
  pinMode(motorPin2, OUTPUT);
  pinMode(motorPin22, OUTPUT);
  pinMode(enableA, OUTPUT);
  pinMode(enableB, OUTPUT);
  servo0.attach(servoPin);

}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);

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


      bool motorAdvance = doc["motor_advance"] == "1";
      bool motorBackward = doc["motor_backward"] == "1";

      if (motorAdvance) {
        digitalWrite(enableA, HIGH);
        digitalWrite(enableB, HIGH);
        digitalWrite(motorPin1, HIGH);
        digitalWrite(motorPin11, LOW);
        digitalWrite(motorPin2, HIGH);
        digitalWrite(motorPin22, LOW);
        Serial.println("Avanzando");
      } else if (motorBackward) {
        digitalWrite(enableA, HIGH);
        digitalWrite(enableB, HIGH); 
        digitalWrite(motorPin1, LOW);
        digitalWrite(motorPin11, HIGH);
        digitalWrite(motorPin2, LOW);
        digitalWrite(motorPin22, HIGH);
        Serial.println("Retrocediendo");
      } else {
        digitalWrite(enableA, LOW);
        digitalWrite(enableB, LOW);
        digitalWrite(motorPin1, LOW);
        digitalWrite(motorPin11, LOW);
        digitalWrite(motorPin2, LOW);
        digitalWrite(motorPin22, LOW);
        Serial.println("Frenando");
      }


      bool servo0Left = doc["servo0_left"] == "1";
      bool servo0Right = doc["servo0_right"] == "1";

      if (servo0Left) {
        servo0.write(45);
        Serial.println("Girando a la izquierda");
      } else if (servo0Right) {
        servo0.write(135);
        Serial.println("Girando a la derecha");
      } else {
        servo0.write(90);
        Serial.println("Linea recta");
      }

    } else {
      Serial.print("Error on HTTP request: ");
      Serial.println(httpResponseCode);
    }

    http.end();
  }

  delay(1000);
}