# Adapter pattern in Laravel

### Adapter pattern nima?

**Adapter pattern** (**wrapper pattern** ham deb nomlanadi) **structural patterns** oilasiga mansub bo'lib, u klaslarni qanday ko'rinishda yozsa, ularni boshqarish va kengaytirish oson bo'lishini tushuntiradi.

Adapter pattern-ida klas interfeysini boshqa interfeysga konvertlab mijoz xohlagan ko'rinishga keltirib beriladi. Aniqroq aytganda, o'zaro birgalikda ishlay olmaydigan klaslarni bir-biriga moslashtirib beradi.

Ba'zi holatlarda, o'zimizda mavjud bo'lgan kodlardan foydalanishni xohlaymiz, ammo uning interfeysi biz yozmoqchi bo'lgan kodga to'g'ri kelmay qoladi. Misol uchun, o'zimiz yozgan kodimizni o'zgartirmay turib, tashqaridan paket yuklab ishlatishga to'g'ri keladi. Ya'ni, yangi klas yozmasdan, o'zimizda mavjud bo'lgani bilan ishlashimiz kerak bo'ladi.

Adapter pattern-da quyidagi qatnashchilar bo'ladi:

* ***Mijoz*** (Client): moslashtirilishi kerak bo'lgan tashqi APIni ishlatmoqchi bo'layotgan klas yoki obyekt
* **Moslashtiruvchi** (Adapter): moslashuvchi va uning mijozi o'rtasida umumiy interfeysni yaratib beradi.
* **Moslashuvchi** (Adaptee): tashqaridan, masalan, tashqi paketdan ulanayotgan obyekt

Adapter pattern-ning eng katta afzalligi - bu mijoz kodini tashqi ulanuvchi resurslardan alohida ajratib berishida.

> Bu pattern-dan kodingiz tez-tez o'zgarib turadigan tashqi API-ga yoki boshqa tashqi klaslarga bog'liq bo'lganda ishlatiladi.

### Masala

Faraz qilaylik, saytimizdagi mahsulotlar omborini tekshiruvchi umumiy interfeys bor bo'lsin.

Bunda, dasturimizda ombordagi mahsulotni tekshiradigan servisimiz bor bo'lib, uni kontrollerda quyidagicha ishlatamiz:

```bash
class StockCheckController extends Controller
{
    protected $service;

    public function __construct(DatabaseStockCheck $databaseStockCheck)
    {
        $this->service = $databaseStockCheck;
    }

    public function index(Request $request)
    {
        $sku = $request->input('sku');
        $stock = $this->service->getStock($sku);

        return response()->json($stock);
    }
}
```

`DatabaseStockCheck` servisi:

```bash
class DatabaseStockCheck
{
    public function getStock($sku)
    {
        $product = Product::whereSku($sku)->first();

        return $product->qty;
    }
}
```

> Bu yerda keltirilgan misol sodda ko'rinishda berilyapti. Aslida esa, repository va shu kabi boshqa klaslardan ham foydalanib ishlanadi.

Yuqoridagi kodda ko'rganingizdek, kontrollerning index metodida mahsulotning `sku`-sini request obyektidan olib, uni `DatabaseStockCheck` serivisiga uzatyapmiz va shu `sku`-ga mos keluvchi mahsulotni bazadan olyapmiz.

`DatabaseStockCheck` servisi, faqatgina, mahsulotni topib, uning bazadagi miqodrini qaytaryapti. Juda oddiy, to'g'rimi?

Biroz vaqt o'tgandan so'ng, menejerimiz kompaniyaning ERP tizimi ma'lumotlar bazasidan ham mahsulot miqdorini tekshirish kerakligini aytdi. Bu talab qo'yilishi bilan bizda ba'zi muammolar paydo bo'ladi:

* Birinchidan, ERP ma'lumotlar bazasiga so'rov yuborishi uchun kontrollerimizni o'zgartirishimiz kerak.
* ERP-ning ma'lumotlar omboridan ma'lumot oluvchi API `getStock()` metodini ishlatganmi yoki yo'q, buni bilmaymiz. Ishlatmagan bo'lsa, API-ni o'zgaritirishni ham so'ray olmaymiz.
* Boshqa servis qo'shib, `DatabaseStockCheck` klasini o'zgartirishimiz kerak.
* ERP-ning API-si bizga kerak bo'lgan ko'rinishdagi ma'lumot tipini berish yoki bermasligini ham bilmaymiz.

Endi faraz qilamiz, ERP API-ning quyidagicha klasi bor bo'lsin. Bu klasni o'zgartira olmaymiz. Undan faqat foydalanishimiz mumkin xolos:

```bash
class Erp
{
    protected $sku;

    public function __construct($sku)
    {
        $this->sku = $sku;
    }

    public function checkStock()
    {
        return [
            'sku' => $this->sku,
            'status' => true,
            'qty' => 101
        ];
    }
}
```

`Erp` klas kodida ko'rganingizdek, bu klas o'zining `checkStock()` metodi orqali omboridagi mahsulot haqidagi ma'lumotni array ko'rinishida olyapti. Ya'ni, tashqi API o'ziga mos kelgan ko'rinishda boshqa nomdagi metod bilan ma'lumotni olyapti. Uni biz xohlaganimizcha o'zgartira olmaymiz.

Aynan shu yerga kelganda, yuqoridagi muammoni Adapter pattern hal qilib beradi.

### Adapter pattern-ni Laravel-da ishlatish

Shu paytgacha omborni tekshirish haqida quyidagi ikkita narsani bilar edi:

* Ombordagi mahsulotni uning sku-si bo'yicha tekshiramiz.
* Ma'lumotlar omboridan yoki API-dan olinadigan ma'lumot butun son ko'rinishida bo'lishi kerak

Birinchi ish, `StockCheckerInterface` interfeysini yaratamiz:

```bash
interface StockCheckerInterface
{
    public function getStock($sku);
}
```

Keyin, `StockCheckerInterface` interfeysini ishlatadigan `DatabaseStockCheckAdapter` klasini yaratamiz:

```bash
class DatabaseStockCheckAdapter implements StockCheckerInterface
{
    public function getStock($sku)
    {
        $product = Product::whereSku($sku)->first();

        return $product->qty;
    }
}
```

So'ngra, `ErpStockCheckAdapter` nomli yangi klas yaratamiz. Bu klas ham `StockCheckerInterface` interfeysini ishlatadi:

```bash
class ErpStockCheckAdapter implements StockCheckerInterface
{
    public function getStock($sku)
    {
        $erp = new Erp($sku);

        $result = $erp->checkStock();

        return $result['qty'];
    }
}
```

Shu yergacha umumiy interfeysni ishlatuvchi ikkita adapter klasni yaratdik. Endi umumiy interfeysni shu klaslarga bog'lab qo'yamiz. Buni `AppServiceProvider`-da bajaramiz:

```bash
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StockCheckerInterface::class, function ($app) {
            switch ($app->make('config')->get('services.stock-checker')) {
                case 'database':
                    return new DatabaseStockCheckAdapter;
                case 'erp':
                    return new ErpStockCheckAdapter;
                default:
                    throw new \RuntimeException("Unknown Stock Checker Service");
            }
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
```

`AppServiceProvider`-da `StockCheckerInterface` interfeysini, konfiguratsiyaga qarab `DatabaseStockCheckAdapter` va `ErpStockCheckAdapter` klaslaridan biriga bog'lab qo'yayapmiz.

Konfiguratsiyani `config/services.php` fayliga yozib qo'yamiz:

```bash
<?php

return [

    'stock-checker' => 'database'
];
```

Va nihoyat, kontrollerimizni `StockCheckerInterface` interfeysiga moslaymiz:

```bash
class StockCheckController extends Controller
{
    protected $service;

    public function __construct(StockCheckerInterface $stockChecker)
    {
        $this->service = $stockChecker;
    }

    public function index(Request $request)
    {
        $sku = $request->input('sku');
        $stock = $this->service->getStock($sku);

        return response()->json($stock);
    }
}
```

### Xulosa
Adapter patter-ni yana wrapper pattern ham deyiladi. Bunga sabab esa u mavjud interfeysni o'ziga moslashtiradi.
Bu pattern-ning kamchiligi shundaki, agar ikkita klas juda ko'p sondagi metodlarga ega bo'lsa, ularni moslashtirish ancha qiyinlashadi va ba'zida imkonsiz ham bo'lib qoladi.

>Adapter pattern doimo mijoz va moslashuvchilarda umumiy maqsad bo'lgandagina ishlatilishi kerak.
