#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Keypad.h>

// ==================== CONFIGURATION ====================
const char* ssid = "SCAR";
const char* password = "scar424*";
const char* baseUrl = "http://192.168.135.101/smart-water-billing/api/";
const char* validateEndpoint = "validate_token.php";
const char* submitUsageEndpoint = "submit_usage.php";
const char* getBalanceEndpoint = "get_balance.php";
const char* getValveStatusEndpoint = "get_valve_status.php";

// Meter ID
String meterId = "2321";

// Hardware pins
const int relayPin = 5;          // D5
const int flowSensorPin = 4;     // D4

// LCD
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Keypad
const byte ROWS = 4;
const byte COLS = 4;
char keys[ROWS][COLS] = {
  {'1','2','3','A'},
  {'4','5','6','B'},
  {'7','8','9','C'},
  {'*','0','#','D'}
};
byte rowPins[ROWS] = {13, 12, 14, 27};
byte colPins[COLS] = {26, 25, 33, 32};
Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);

// Token buffer
String token = "";
const int maxTokenLength = 12;

// Flow sensor
volatile int pulseCount = 0;
float flowRate = 0.0;          // L/min
float litersSinceLastSend = 0.0;
unsigned long lastFlowUpdate = 0;
const unsigned long flowUpdateInterval = 1000; // update flow every second
unsigned long lastSendTime = 0;
const unsigned long sendInterval = 10000;      // send usage every 10 seconds
unsigned long lastBalanceCheck = 0;
const unsigned long balanceCheckInterval = 20000; // check balance every 20s
unsigned long lastValveCheck = 0;
const unsigned long valveCheckInterval = 10000;  // check valve every 10 seconds

bool valveOpen = false;
float currentBalance = 0.0;

// ==================== INTERRUPT SERVICE ====================
void IRAM_ATTR pulseCounter() {
  pulseCount++;
}

// ==================== SETUP ====================
void setup() {
  Serial.begin(115200);

  pinMode(relayPin, OUTPUT);
  digitalWrite(relayPin, HIGH);
  valveOpen = false;

  pinMode(flowSensorPin, INPUT_PULLUP);
  attachInterrupt(digitalPinToInterrupt(flowSensorPin), pulseCounter, FALLING);

  lcd.init();
  lcd.backlight();
  lcd.print("Smart Water");
  lcd.setCursor(0, 1);
  lcd.print("Billing System");
  delay(2000);
  lcd.clear();

  lcd.setCursor(0, 0);
  lcd.print("Connecting WiFi");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Connected");
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("WiFi OK");
  delay(1000);
  lcd.clear();

  updateDisplay();
  checkBalanceAndControlValve();
  checkValveStatus();
}

// ==================== MAIN LOOP ====================
void loop() {
  char key = keypad.getKey();
  if (WiFi.status() != WL_CONNECTED) {
    WiFi.reconnect();
  }
  if (key) handleKeypad(key);

  handleFlow();

  unsigned long now = millis();
  if (now - lastSendTime >= sendInterval) {
    sendUsage();
    lastSendTime = now;
  }
  if (now - lastBalanceCheck >= balanceCheckInterval) {
    checkBalanceAndControlValve();
    lastBalanceCheck = now;
  }
  if (now - lastValveCheck >= valveCheckInterval) {
    checkValveStatus();
    lastValveCheck = now;
  }

  updateDisplay();
}

// ==================== KEYPAD HANDLING ====================
void handleKeypad(char key) {
  if (key == '#') {
    if (token.length() > 0) {
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Validating...");
      String result = validateToken(token);
      lcd.clear();
      if (result == "valid") {
        lcd.setCursor(0, 0);
        lcd.print("Token Valid!");
        lcd.setCursor(0, 1);
        lcd.print("Balance Updated");
        delay(2000);
        checkBalanceAndControlValve();
        checkValveStatus();
      } else {
        lcd.setCursor(0, 0);
        lcd.print("Invalid Token");
        lcd.setCursor(0, 1);
        lcd.print("Try Again");
        delay(2000);
      }
      token = "";
      lcd.clear();
      updateDisplay();
    }
  }
  else if (key == '*') {
    if (token.length() > 0) {
      token.remove(token.length() - 1);
    }
  }
  else if (key >= '0' && key <= '9') {
    if (token.length() < maxTokenLength) {
      token += key;
    }
  }
}

// ==================== LCD UPDATES (fixed) ====================
void updateDisplay() {
  // Line 1: Balance (format: "Bal: 12.34m3")
  String line1 = "Bal: ";
  if (currentBalance >= 0) {
    line1 += String(currentBalance, 2);
    line1 += "m3";
  } else {
    line1 += "--.--m3";
  }
  while (line1.length() < 16) line1 += " ";
  lcd.setCursor(0, 0);
  lcd.print(line1);

  // Line 2: Token or Flow
  String line2;
  if (token.length() > 0) {
    line2 = ">" + token;
  } else {
    line2 = "Flow: " + String(flowRate, 2) + "L/m";
  }
  while (line2.length() < 16) line2 += " ";
  lcd.setCursor(0, 1);
  lcd.print(line2);
}

// ==================== FLOW SENSOR PROCESSING ====================
void handleFlow() {
  unsigned long now = millis();
  if (now - lastFlowUpdate >= flowUpdateInterval) {
    detachInterrupt(digitalPinToInterrupt(flowSensorPin));
    float freq = pulseCount;
    flowRate = freq * 0.13333;
    float litersThisInterval = pulseCount / 450.0;
    litersSinceLastSend += litersThisInterval;
    pulseCount = 0;
    lastFlowUpdate = now;
    attachInterrupt(digitalPinToInterrupt(flowSensorPin), pulseCounter, FALLING);
  }
}

// ==================== USAGE SUBMISSION ====================
void sendUsage() {
  if (litersSinceLastSend <= 0.001) return;
  if (WiFi.status() != WL_CONNECTED) return;

  HTTPClient http;
  String url = String(baseUrl) + submitUsageEndpoint;
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  String payload = "{\"meter_id\":\"" + meterId + "\",\"water_used\":" + String(litersSinceLastSend, 3) + "}";
  int httpCode = http.POST(payload);
  if (httpCode == 200) {
    String response = http.getString();
    Serial.print("Usage sent: ");
    Serial.print(litersSinceLastSend, 3);
    Serial.println(" L");
    if (response.indexOf("\"valve_status\":\"closed\"") != -1 && valveOpen) {
      closeValve();
    }
    litersSinceLastSend = 0;
  } else {
    Serial.print("Failed to send usage, HTTP: ");
    Serial.println(httpCode);
  }
  http.end();
}

// ==================== BALANCE & VALVE CONTROL ====================
void checkBalanceAndControlValve() {
  if (WiFi.status() != WL_CONNECTED) return;
  HTTPClient http;
  String url = String(baseUrl) + getBalanceEndpoint + "?meter_id=" + meterId;
  http.begin(url);
  int httpCode = http.GET();
  if (httpCode == 200) {
    String response = http.getString();
    int balanceStart = response.indexOf("\"balance\":");
    if (balanceStart != -1) {
      balanceStart += 10;
      int balanceEnd = response.indexOf(",", balanceStart);
      if (balanceEnd == -1) balanceEnd = response.indexOf("}", balanceStart);
      float balance = response.substring(balanceStart, balanceEnd).toFloat();
      currentBalance = balance;
      if (balance > 0 && !valveOpen) openValve();
      else if (balance <= 0 && valveOpen) closeValve();
    }
    if (response.indexOf("\"valve_status\":\"closed\"") != -1 && valveOpen) closeValve();
  }
  http.end();
}

// ==================== VALVE STATUS CHECK ====================
void checkValveStatus() {
  if (WiFi.status() != WL_CONNECTED) return;
  HTTPClient http;
  String url = String(baseUrl) + getValveStatusEndpoint + "?meter_id=" + meterId;
  http.begin(url);
  int httpCode = http.GET();
  if (httpCode == 200) {
    String response = http.getString();
    Serial.print("Valve status response: ");
    Serial.println(response);

    int balanceStart = response.indexOf("\"balance\":");
    if (balanceStart != -1) {
      balanceStart += 10;
      int balanceEnd = response.indexOf(",", balanceStart);
      if (balanceEnd == -1) balanceEnd = response.indexOf("}", balanceStart);
      float balance = response.substring(balanceStart, balanceEnd).toFloat();
      currentBalance = balance;
    }

    int statusStart = response.indexOf("\"valve_status\":\"");
    if (statusStart != -1) {
      statusStart += 16;
      int statusEnd = response.indexOf("\"", statusStart);
      String valveStatus = response.substring(statusStart, statusEnd);
      if (valveStatus == "open" && !valveOpen) openValve();
      else if (valveStatus == "closed" && valveOpen) closeValve();
    }
  } else {
    Serial.print("Failed to check valve status, HTTP: ");
    Serial.println(httpCode);
  }
  http.end();
}

// ==================== VALVE CONTROL ====================
void openValve() {
  digitalWrite(relayPin, LOW);
  valveOpen = true;
  Serial.println("Valve OPEN");
}

void closeValve() {
  digitalWrite(relayPin, HIGH);
  valveOpen = false;
  Serial.println("Valve CLOSED");
}

// ==================== TOKEN VALIDATION ====================
String validateToken(String tokenCode) {
  if (WiFi.status() != WL_CONNECTED) return "invalid";
  HTTPClient http;
  String url = String(baseUrl) + validateEndpoint;
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  String payload = "{\"token_code\":\"" + tokenCode + "\",\"meter_id\":\"" + meterId + "\"}";
  int httpCode = http.POST(payload);
  if (httpCode == 200) {
    String response = http.getString();
    http.end();
    if (response.indexOf("\"success\":true") != -1) return "valid";
  } else {
    http.end();
  }
  return "invalid";
}