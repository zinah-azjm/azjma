# عائلتي – Laravel API

Laravel 12 وSanctum API لتطبيق العائلة. يحتوي المشروع على تسجيل ولي الأمر والطفل، المهام، نقاط الألعاب، المكافآت، وصلاحيات تفصل العائلات عن بعضها.

## تشغيل محلي سريع

المجلد يحتوي الاعتماديات وملف `.env` مضبوطاً على قاعدة `family_quest` في MySQL/MariaDB الخاص بـ XAMPP. تم إنشاء القاعدة وتنفيذ جميع migrations. لتشغيل الخادم، شغّل MySQL من XAMPP ثم:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

بعدها افتح تطبيق Flutter. للحساب الأول استخدم «إنشاء عائلة جديدة» في التطبيق.

## إعداد MySQL الحالي

الإعداد المستخدم حالياً في `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=family_quest
DB_USERNAME=root
DB_PASSWORD=
```

الجداول موجودة بالفعل. عند إضافة migrations جديدة شغّل `php artisan migrate`.

## التثبيت على جهاز آخر

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

اضبط قاعدة البيانات في `.env` قبل `migrate`. لا تنشر `.env` أو `vendor` أو قاعدة SQLite المحلية.

## مسارات API الأساسية

`POST /api/register`, `POST /api/login`, `GET /api/me`, `POST /api/children`, `GET|POST /api/tasks`, `POST /api/tasks/{id}/submit`, `POST /api/tasks/{id}/approve`, `GET /api/quiz`, `POST /api/quiz/answer`, `GET|POST /api/rewards`, `POST /api/rewards/{id}/request`, `GET /api/reward-requests`, `POST /api/reward-requests/{id}/approve`.

المسارات المحمية تستخدم `Authorization: Bearer <token>`. الخادم يتحقق من دور الأهل أو الطفل ومن انتماء البيانات لنفس العائلة. اعتماد النقاط وخصمها يتم داخل معاملات قاعدة البيانات.
