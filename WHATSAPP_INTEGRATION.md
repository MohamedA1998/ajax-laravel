# دليل دمج واتساب مع Laravel (WhatsApp Integration Guide)

هذا الدليل يشرح خطوة بخطوة كيفية ربط تطبيق Laravel مع WhatsApp Business API.

## المتطلبات الأساسية
1.  حساب فيسبوك (Meta).
2.  تطبيق Laravel (تم إعداده في هذا المشروع).
3.  بيئة محلية متاحة عبر الإنترنت (مثل Ngrok) لاختبار Webhook، أو استضافة حقيقية.

## الخطوة 1: إعداد حساب Meta Developer
1.  اذهب إلى [developers.facebook.com](https://developers.facebook.com/).
2.  سجل الدخول بحسابك واضغط على "My Apps" ثم "Create App".
3.  اختر النوع **Other** > **Business**.
4.  املأ بيانات التطبيق واضغط "Create App".
5.  في لوحة التحكم (Dashboard)، ابحث عن منتج **WhatsApp** واضغط "Set up".

## الخطوة 2: الحصول على بيانات الاعتماد (Credentials)
1.  بعد الإعداد، ستنتقل إلى صفحة **Getting Started** في قسم WhatsApp.
2.  ستجد:
    *   **Temporary Access Token**: توكن مؤقت (صلاحيته 24 ساعة). للاستخدام الدائم تحتاج لإنشاء System User.
    *   **Phone Number ID**: معرف رقم الهاتف.
    *   **WhatsApp Business Account ID**: معرف حساب الواتساب.
3.  أضف رقم هاتفك الحقيقي في قسم "To" لتجربة الإرسال واضغط "Send Message" للتأكد من العمل.

## الخطوة 3: إعداد المشروع (Laravel)

لقد قمنا بالفعل بإنشاء الملفات اللازمة في هذا المشروع.

### 1. إعداد متغيرات البيئة
أضف البيانات التالية إلى ملف `.env`:

```env
WHATSAPP_TOKEN="ضع_الـ_Access_Token_هنا"
WHATSAPP_PHONE_NUMBER_ID="ضع_Phone_Number_ID_هنا"
WHATSAPP_VERIFY_TOKEN="my_secret_verify_token"
```

*ملاحظة: `WHATSAPP_VERIFY_TOKEN` هي كلمة سر تختارها أنت (مثلاً "laravel_whatsapp_123") لتأمين الـ Webhook.*

### 2. الكود البرمجي
*   **Service**: تم إنشاء `app/Services/WhatsAppService.php` لإرسال الرسائل.
*   **Controller**: تم إنشاء `app/Http/Controllers/WhatsAppController.php` لاستقبال الرسائل والتحقق من الـ Webhook.
*   **Routes**: تم إضافة مسارات `/whatsapp/webhook` في `routes/web.php`.
*   **CSRF**: تم استثناء الرابط من الحماية في `bootstrap/app.php` ليتمكن فيسبوك من إرسال البيانات.

## الخطوة 4: إعداد الـ Webhook
لكي يستقبل تطبيقك الرسائل من واتساب، يجب إعداد الـ Webhook.

1.  في لوحة تحكم Meta Developer، اذهب إلى قسم **WhatsApp** > **Configuration**.
2.  اضغط على **Edit** في قسم **Webhook**.
3.  سيطلب منك:
    *   **Callback URL**: رابط تطبيقك. إذا كنت تعمل محلياً، استخدم Ngrok.
        *   مثال: `https://your-domain.com/whatsapp/webhook`
    *   **Verify Token**: نفس الكلمة التي وضعتها في `.env` (مثلاً `my_secret_verify_token`).
4.  اضغط **Verify and Save**. إذا كان الرابط يعمل والتوكن صحيح، سيتم الحفظ بنجاح.
5.  بعد الحفظ، اضغط على **Manage** في قسم **Webhook Fields** واشترك في الأحداث التي تريدها (أهمها `messages`).

## كيفية الاستخدام

### إرسال رسالة نصية
يمكنك استخدام الخدمة في أي مكان في تطبيقك (Controller, Job, etc.):

```php
use App\Services\WhatsAppService;

public function sendHello(WhatsAppService $service)
{
    $service->sendMessage('201xxxxxxxxx', 'Hello from Laravel!');
}
```

### استقبال الرسائل
عندما يرسل شخص رسالة لرقم الواتساب الخاص بالنشاط التجاري، سيقوم فيسبوك بإرسال طلب `POST` إلى `/whatsapp/webhook`.
الكود الحالي في `WhatsAppController` يقوم بـ:
1.  استلام الرسالة.
2.  الرد عليها بنفس النص (Echo) كإثبات مفهوم.

## ملاحظات هامة للإنتاج (Production)
1.  **Token الدائم**: التوكن في "Getting Started" مؤقت. للإنتاج، اذهب إلى "Business Settings" > "Users" > "System Users"، أنشئ مستخدم، وأعطه صلاحية `whatsapp_business_messaging` واستخرج التوكن.
2.  **توثيق الحساب**: لإرسال رسائل لأي رقم (وليس فقط الأرقام التجريبية)، يجب توثيق النشاط التجاري (Business Verification).
3.  **قوالب الرسائل (Templates)**: لبدء محادثة مع عميل (بعد مرور 24 ساعة من آخر رسالة منه)، يجب استخدام Template Messages معتمدة من فيسبوك.

---
تم إعداد المشروع بنجاح ليكون جاهزاً للربط!
