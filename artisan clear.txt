php artisan optimize:clear && php artisan cache:clear

หรือ

🔹 เคลียร์ทุกอย่าง (เหมือน reset cache)
php artisan optimize:clear


เคลียร์ config, route, view, event cache ทั้งหมดในครั้งเดียว

🔹 แยกเคลียร์ทีละส่วน

เคลียร์ config cache

php artisan config:clear


เคลียร์ route cache

php artisan route:clear


เคลียร์ view cache

php artisan view:clear


เคลียร์ event cache

php artisan event:clear

🔹 ใช้ร่วมกันบ่อยสุด
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear