# 🛡️ คู่มือเตรียมความพร้อมและปรับแต่งความปลอดภัยสำหรับการทำ VA Scan (Vulnerability Assessment)
### โปรเจกต์: AOPOD (Laravel 12 / PHP 8.2 / AlmaLinux 9)

การทำ **VA Scan (Vulnerability Assessment)** คือขั้นตอนการตรวจสอบช่องโหว่ความปลอดภัยของระบบ ทั้งในระดับ **แอปพลิเคชัน (Application)** และ **โครงสร้างเซิร์ฟเวอร์ (Infrastructure)** ก่อนนำระบบขึ้นใช้งานจริง
ด้านล่างนี้คือรายการตรวจสอบ (Checklist) และแนวทางการปรับแต่งระบบเพื่อช่วยให้ระบบ AOPOD **ผ่านการทดสอบ VA Scan ในระดับคะแนนสูงสุด**

---

## 📂 ส่วนที่ 1: การตั้งค่าความปลอดภัยระดับแอปพลิเคชัน (Laravel App)

Laravel เป็นเฟรมเวิร์กที่มีระบบความปลอดภัยในตัวที่ดีเยี่ยม (Secure by Design) หากตั้งค่าและเขียนโค้ดตามมาตรฐาน จะสามารถผ่านหัวข้อการสแกนในระดับแอปพลิเคชันได้อย่างราบรื่น:

### 1. ปิดโหมดผู้พัฒนาสำหรับเซิร์ฟเวอร์จริง (Disabled Debug Mode) ⚠️ [ความสำคัญ: สูงมาก]
*   **ความเสี่ยงที่สแกนเจอ:** Verbose Error Message / Information Disclosure (เผยแพร่ข้อมูลการทำงานและรหัสผ่านฐานข้อมูลเมื่อระบบเกิดข้อผิดพลาด)
*   **การแก้ไข (ในไฟล์ `.env` บน Server จริง):**
    ```env
    APP_ENV=production
    APP_DEBUG=false
    LOG_LEVEL=error
    ```

### 2. การป้องกัน SQL Injection (SQLi)
*   **มาตรฐานของ Laravel:** Eloquent ORM และ Query Builder ใช้ระบบ **PDO Parameter Binding** ในตัวโดยอัตโนมัติ ซึ่งป้องกันการโจมตี SQL Injection ได้ 100%
*   **สิ่งที่ต้องระวังตอนเขียนโค้ด:** หลีกเลี่ยงการใช้คำสั่งดิบ (Raw Queries) ที่ใช้การเชื่อมต่อสตริงโดยตรง เช่น:
    *   ❌ *แบบอันตราย:* `DB::select("SELECT * FROM users WHERE id = " . $userInput)`
    *   ✔️ *แบบปลอดภัย (ต้องใช้ Binding):* `DB::select("SELECT * FROM users WHERE id = ?", [$userInput])` หรือ `DB::table('users')->where('id', $userInput)->get()`

### 3. การป้องกัน Cross-Site Scripting (XSS)
*   **มาตรฐานของ Laravel:** การแสดงผลในไฟล์เทมเพลต Blade โดยใช้เครื่องหมาย `{{ $variable }}` จะทำการ **Escape อักขระพิเศษ HTML โดยอัตโนมัติ** ทำให้สคริปต์อันตรายไม่ทำงาน
*   **สิ่งที่ต้องระวังตอนเขียนโค้ด:** หลีกเลี่ยงการแสดงผลด้วยเครื่องหมาย `{!! $variable !!}` (Unescaped) เว้นแต่ข้อมูลนั้นจะถูกกรองความปลอดภัยด้วยฟังก์ชันอย่าง `purify` หรือเป็นข้อมูลจากระบบที่เชื่อถือได้เท่านั้น

### 4. การป้องกัน Cross-Site Request Forgery (CSRF)
*   **มาตรฐานของ Laravel:** มีการบังคับใช้ Middleware `VerifyCsrfToken` เป็นค่าเริ่มต้น
*   **สิ่งที่ต้องทำ:** ตรวจสอบว่าฟอร์มรับข้อมูล (POST, PUT, DELETE) ทุกจุดใน Blade มีการประกาศ `@csrf` เสมอ เพื่อส่งคีย์สุ่มความปลอดภัยป้องกันการจู่โจมจากภายนอก

### 5. ตั้งค่าคุกกี้เซสชันให้ปลอดภัย (Secure Session Cookies)
*   **การแก้ไข (ในไฟล์ `.env` บน Server จริง):** ป้องกันการขโมยคุกกี้ผ่านสคริปต์ (Session Hijacking)
    ```env
    SESSION_SECURE_COOKIE=true  # เปิดใช้เมื่อเซิร์ฟเวอร์ใช้ HTTPS เท่านั้น
    SESSION_HTTP_ONLY=true      # ป้องกันไม่ให้ Javascript เข้าถึงคุกกี้เซสชัน
    ```

---

## 🖥️ ส่วนที่ 2: การตั้งค่าความปลอดภัยระดับเว็บเซิร์ฟเวอร์ (Apache / httpd)

เมื่อทำ VA Scan เครื่องสแกนจะตรวจสอบการทำงานของ Web Server (Apache) ซึ่งสามารถตั้งค่าปิดช่องโหว่ทั่วไปได้ดังนี้:

### 1. ปิดการแสดงรายชื่อไฟล์ในไดเรกทอรี (Directory Indexing) ⚠️ [ความสำคัญ: สูง]
*   **ความเสี่ยงที่สแกนเจอ:** Directory Traversal / Information Exposure (ผู้ใช้ทั่วไปแอบเปิดดูรายชื่อไฟล์อื่นๆ ในโฟลเดอร์ผ่านเว็บเบราว์เซอร์ได้)
*   **การแก้ไข (ปรับปรุงในไฟล์ `/etc/httpd/conf.d/aopod.conf`):** 
    เปลี่ยนจาก `Options Indexes` เป็นการปิดใช้งานผ่านเครื่องหมายลบ `-Indexes`
    ```apache
    <Directory /var/www/html/aopod/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ```

### 2. ซ่อนเวอร์ชันและข้อมูลระบบของเซิร์ฟเวอร์ (Hiding Server Banner)
*   **ความเสี่ยงที่สแกนเจอ:** Server Banner Grabbing (ผู้โจมตีสแกนหาจุดอ่อนจากเวอร์ชันที่แสดงของ Apache และ OS)
*   **การแก้ไข:** แก้ไขไฟล์คอนฟิกูเรชันหลักของ Apache `/etc/httpd/conf/httpd.conf` และเพิ่ม/แก้ไขค่าด้านล่างนี้ที่ส่วนท้ายไฟล์:
    ```apache
    ServerTokens Prod
    ServerSignature Off
    ```

### 3. เพิ่มการส่ง HTTP Security Headers
*   **การแก้ไข:** การเพิ่ม Headers เพื่อป้องกันการโจมตีรูปแบบ Clickjacking, MIME Sniffing และ XSS (ใส่ไว้ภายใน VirtualHost ในไฟล์ `/etc/httpd/conf.d/aopod.conf`):
    ```apache
    <IfModule mod_headers.c>
        # ป้องกันไม่ให้เว็บไซต์ถูกเปิดขึ้นมาใน iframe ของเว็บอื่น (ป้องกัน Clickjacking)
        Header always set X-Frame-Options "SAMEORIGIN"

        # บังคับให้บราวเซอร์ตรวจสอบประเภทไฟล์จริง ป้องกันการรันสคริปต์ปลอมแปลง (MIME Sniffing)
        Header always set X-Content-Type-Options "nosniff"

        # บังคับเปิดใช้งานตัวกรอง XSS บนเว็บเบราว์เซอร์ของผู้ใช้
        Header always set X-XSS-Protection "1; mode=block"

        # จัดการความปลอดภัยด้านการส่งข้อมูลอ้างอิงต้นทาง (Referrer-Policy)
        Header always set Referrer-Policy "no-referrer-when-downgrade"
    </IfModule>
    ```

---

## 🔒 ส่วนที่ 3: การตั้งค่าความปลอดภัย SSL/TLS และฐานข้อมูล (Database)

### 1. การเข้ารหัสการสื่อสารข้อมูล (SSL/TLS)
*   **มาตรฐานความปลอดภัย:** ระบบจะต้องใช้งานโปรโตคอล **HTTPS เท่านั้น** 
*   **ข้อเสนอแนะสำหรับการทำ VA Scan:**
    *   **ห้ามใช้** TLS เวอร์ชันเก่า (TLS 1.0 และ TLS 1.1) เนื่องจากมีช่องโหว่ความปลอดภัยระดับร้ายแรง
    *   **อนุญาตเฉพาะ** TLS 1.2 และ TLS 1.3
    *   ใช้โปรแกรม **Certbot (Let's Encrypt)** หรือ SSL Certificate ที่น่าเชื่อถือในการติดตั้งและเข้ารหัส

### 2. การจำกัดสิทธิ์ผู้ใช้ฐานข้อมูล (Database Hardening)
*   **การแก้ไข:**
    *   **ห้ามใช้** บัญชีผู้ใช้ฐานข้อมูลที่เป็น `root` ในระบบงานจริงเด็ดขาด
    *   สร้างบัญชีผู้ใช้งานฐานข้อมูลเฉพาะสำหรับระบบ AOPOD (เช่น `aopod`) และจำกัดสิทธิ์ให้อ่านเขียนได้ **เฉพาะภายในฐานข้อมูล `aopod` เท่านั้น**
    *   ปิดไม่ให้พอร์ตฐานข้อมูล (3306) เข้าถึงได้จากอินเทอร์เน็ตภายนอก (อนุญาตให้เข้าถึงได้เฉพาะจากโฮสต์ `127.0.0.1` หรือเซิร์ฟเวอร์ที่ได้รับอนุญาตเท่านั้น)

---

### 📝 สรุปภาพรวมสำหรับผู้ดูแลระบบ
คุณสามารถนำแนวทางและคำสั่งตั้งค่าในหน้านี้ ไปใช้งานร่วมกับไฟล์คู่มือการติดตั้ง [install.txt](file:///d:/Projec%20Laravel/aopod/install.txt) เพื่อให้เซิร์ฟเวอร์ AlmaLinux 9 และแอปพลิเคชันมีความปลอดภัยสูงสุด พร้อมรองรับการตรวจสอบจากสถาบันการเงินหรือหน่วยงานตรวจสอบความปลอดภัยไอที (VA Scan & PenTest) ได้อย่างมีประสิทธิภาพครับ!
