<?php

namespace Modules\Cms\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Cms\Models\Blog;
use Modules\Cms\Models\BlogCategory;
use Modules\Cms\Models\Faq;

class CmsBlogAndFaqDemoSeeder extends Seeder
{
    /**
     * Seed 4 blog categories, 10 blogs, and 6 FAQs (demo content).
     */
    public function run(): void
    {
        $categories = collect([
            [
                'name' => [
                    'en' => 'Market Insights',
                    'ar' => 'رؤى السوق',
                ],
                'slug' => 'market-insights',
                'add_to_navbar' => true,
            ],
            [
                'name' => [
                    'en' => 'Buying & Selling',
                    'ar' => 'الشراء والبيع',
                ],
                'slug' => 'buying-selling',
                'add_to_navbar' => true,
            ],
            [
                'name' => [
                    'en' => 'Investment Tips',
                    'ar' => 'نصائح استثمارية',
                ],
                'slug' => 'investment-tips',
                'add_to_navbar' => false,
            ],
            [
                'name' => [
                    'en' => 'Lifestyle & Neighborhoods',
                    'ar' => 'نمط الحياة والأحياء',
                ],
                'slug' => 'lifestyle-neighborhoods',
                'add_to_navbar' => false,
            ],
        ])->map(fn (array $attrs) => BlogCategory::factory()->create($attrs));

        $blogSeeds = [
            ['slug' => 'how-to-read-a-market-report', 'title' => ['en' => 'How to Read a Housing Market Report', 'ar' => 'كيف تقرأ تقرير سوق الإسكان']],
            ['slug' => 'price-trends-this-quarter', 'title' => ['en' => 'Price Trends Our Buyers Saw This Quarter', 'ar' => 'اتجاهات الأسعار التي رآها المشترون هذا الربع']],
            ['slug' => 'offer-checklist-first-home', 'title' => ['en' => 'Offer Checklist for First-Time Home Buyers', 'ar' => 'قائمة تحقق للعرض لمن يشترون منزلهم الأول']],
            ['slug' => 'staging-secrets-that-work', 'title' => ['en' => 'Staging Secrets That Actually Close Deals', 'ar' => 'أسرار التجهيز التي تنجز الصفقات']],
            ['slug' => 'rent-or-buy-when-relocating', 'title' => ['en' => 'Rent or Buy When Relocating for Work?', 'ar' => 'الإيجار أم الشراء عند الانتقال للعمل؟']],
            ['slug' => 'cap-rate-basics-rentals', 'title' => ['en' => 'Cap Rate Basics for Residential Rentals', 'ar' => 'أساسيات معدل العائد للعقارات السكنية المؤجرة']],
            ['slug' => 'portfolio-diversification-ideas', 'title' => ['en' => 'Portfolio Diversification Ideas Beyond Apartments', 'ar' => 'أفكار لتنويع المحفظة بعيداً عن الشقق']],
            ['slug' => 'family-friendly-neighborhoods-guide', 'title' => ['en' => 'What Makes a Neighborhood Family-Friendly?', 'ar' => 'ما الذي يجعل الحي مناسباً للعائلات؟']],
            ['slug' => 'walkability-vs-quiet-streets', 'title' => ['en' => 'Walkability vs. Quiet Streets: How to Choose', 'ar' => 'القابلية للمشي أم الهدوء: كيف تختار']],
            ['slug' => 'eco-features-buyers-love', 'title' => ['en' => 'Eco Features Buyers Ask About Most', 'ar' => 'أبرز الميزات البيئية التي يطلبها المشترون']],
        ];

        foreach ($blogSeeds as $index => $seed) {
            Blog::factory()
                ->published()
                ->create([
                    'category_id' => $categories->get($index % 4)?->id,
                    'title' => $seed['title'],
                    'slug' => $seed['slug'],
                    'featured' => $index < 4,
                    'visits' => ($index + 1) * 120,
                ]);
        }

        $faqSeeds = [
            [
                'rank' => 10,
                'question' => [
                    'en' => 'How do I schedule a property viewing?',
                    'ar' => 'كيف أحجز موعداً لمعاينة عقار؟',
                ],
                'answer' => [
                    'en' => '<p>Use the contact form on the listing or call our office. We confirm availability with the seller and send you a calendar invite with location details.</p>',
                    'ar' => '<p>استخدم نموذج التواصل في الإعلان أو اتصل بمكتبنا. نؤكد المواعيد مع البائع ونرسل لك تفاصيل الموقع.</p>',
                ],
            ],
            [
                'rank' => 20,
                'question' => [
                    'en' => 'What fees should I expect when buying?',
                    'ar' => 'ما الرسوم المتوقعة عند الشراء؟',
                ],
                'answer' => [
                    'en' => '<p>Beyond the purchase price, budget for transfer taxes, notary or legal fees, appraisal, and lender charges if you finance. Your advisor will give a line-by-line estimate.</p>',
                    'ar' => '<p>بالإضافة لثمن الشراء، خصص مبالغ للرسوم النقلية والتوثيق والتقييم ورسوم التمويل إن وجدت.</p>',
                ],
            ],
            [
                'rank' => 30,
                'question' => [
                    'en' => 'Can I submit an offer below asking price?',
                    'ar' => 'هل يمكن تقديم عرض أقل من السعر المعلن؟',
                ],
                'answer' => [
                    'en' => '<p>Yes, where market conditions allow. We help you interpret comparable sales and seller motivation so your offer is credible.</p>',
                    'ar' => '<p>نعم عندما يسمح السوق. نساعدك على قراءة العقود المشابهة ودوافع البائع لتقديم عرض مقنع.</p>',
                ],
            ],
            [
                'rank' => 40,
                'question' => [
                    'en' => 'Do you list rental properties as well?',
                    'ar' => 'هل تعرضون عقارات للإيجار أيضاً؟',
                ],
                'answer' => [
                    'en' => '<p>Yes. Filter listings by tenure type or ask our team for shortlist options that match your budget and lease length.</p>',
                    'ar' => '<p>نعم. يمكن تصفية الإعلانات حسب نوع التأجير أو طلب قائمة مختارة من فريقنا.</p>',
                ],
            ],
            [
                'rank' => 50,
                'question' => [
                    'en' => 'How long does a typical closing take?',
                    'ar' => 'كم تستغرق إجراءات الإغلاق عادة؟',
                ],
                'answer' => [
                    'en' => '<p>It varies by financing and local rules—often between three and eight weeks from accepted offer. Cash purchases can move faster.</p>',
                    'ar' => '<p>تختلف حسب التمويل والقوانين المحلية—غالباً بين ثلاثة وثمانية أسابيع بعد قبول العرض، والشراء النقدي أسرع.</p>',
                ],
            ],
            [
                'rank' => 60,
                'question' => [
                    'en' => 'Where can I find new listings first?',
                    'ar' => 'أين أجد العروض الجديدة أولاً؟',
                ],
                'answer' => [
                    'en' => '<p>Subscribe to alerts on our site and follow featured posts on the blog—we publish summaries whenever notable inventory hits the market.</p>',
                    'ar' => '<p>فعّل التنبيهات في الموقع وتابع المدونة لملخصات العروض الجديدة.</p>',
                ],
            ],
        ];

        foreach ($faqSeeds as $seed) {
            Faq::factory()->published()->create($seed);
        }
    }
}
