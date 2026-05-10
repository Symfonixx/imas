<?php

namespace Modules\Corporate\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Corporate\Models\CorporateService;
use Modules\Corporate\Models\Team;
use Modules\Corporate\Models\Testimonial;

class CorporateDemoContentSeeder extends Seeder
{
    /**
     * Seed 4 corporate services, 6 testimonials, and 6 team members.
     */
    public function run(): void
    {
        $services = [
            [
                'slug' => 'buyer-representation',
                'featured' => true,
                'visits' => 420,
                'title' => [
                    'en' => 'Buyer Representation',
                    'ar' => 'تمثيل المشتري',
                ],
                'description' => [
                    'en' => 'Guidance from search through closing so you never negotiate alone.',
                    'ar' => 'مرافقة من البحث حتى الإغلاق حتى لا تتفاوض وحدك.',
                ],
                'content' => [
                    'en' => '<p>We align tours with your budget and commute goals, review disclosures with you, and coordinate inspectors so offers stay on schedule.</p>',
                    'ar' => '<p>نوافق الجولات مع ميزانيتك وأهداف التنقل، ونراجع الإفصاحات معك، وننسّق المفتشين ليبقى العرض في الموعد.</p>',
                ],
            ],
            [
                'slug' => 'seller-marketing',
                'featured' => true,
                'visits' => 610,
                'title' => [
                    'en' => 'Seller Marketing & Pricing',
                    'ar' => 'تسويق البائع والتسعير',
                ],
                'description' => [
                    'en' => 'Data-backed pricing, media-ready listings, and targeted outreach.',
                    'ar' => 'تسعير قائم على البيانات وقوائم جاهزة للإعلام وتوجيه متفرّغ للجمهور المناسب.',
                ],
                'content' => [
                    'en' => '<p>Comparable analysis, staging referrals, and omnichannel promotion shorten days-on-market without leaving money on the table.</p>',
                    'ar' => '<p>تحليل المقارنات، إحالات التجهيز، وترويج متعدد القنوات يقلل أيام العرض دون التفريط بالسعر.</p>',
                ],
            ],
            [
                'slug' => 'investment-advisory',
                'featured' => false,
                'visits' => 290,
                'title' => [
                    'en' => 'Investment Advisory',
                    'ar' => 'الاستشارات الاستثمارية',
                ],
                'description' => [
                    'en' => 'Cash-flow modeling and exit strategies for residential portfolios.',
                    'ar' => 'نمذجة التدفقات النقدية واستراتيجيات الخروج للمحافظ السكنية.',
                ],
                'content' => [
                    'en' => '<p>We stress-test rents, capex, and financing scenarios so acquisitions match your risk appetite.</p>',
                    'ar' => '<p>نختبر ضغط الإيجارات والمصاريف الرأسمالية وسيناريوهات التمويل لتتوافق الشراء مع تحمّلك للمخاطر.</p>',
                ],
            ],
            [
                'slug' => 'relocation-concierge',
                'featured' => false,
                'visits' => 180,
                'title' => [
                    'en' => 'Relocation Concierge',
                    'ar' => 'خدمة الانتقال',
                ],
                'description' => [
                    'en' => 'School districts, temporary housing, and employer paperwork handled end-to-end.',
                    'ar' => 'أحياء المدارس، السكن المؤقت، وأوراق جهة العمل بإدارة متكاملة.',
                ],
                'content' => [
                    'en' => '<p>Ideal for executives changing cities—we coordinate timelines across HR, movers, and lenders.</p>',
                    'ar' => '<p>مثالية للتنفيذيين الذين يغيرون المدن — ننسّق الجداول بين الموارد البشرية والنقل والتمويل.</p>',
                ],
            ],
        ];

        foreach ($services as $data) {
            CorporateService::factory()->published()->create(array_merge([
                'image' => 'corporate/services/placeholder.jpg',
                'meta_image' => null,
                'meta_title' => [
                    'en' => $data['title']['en'].' | IMas',
                    'ar' => $data['title']['ar'].' | IMas',
                ],
                'meta_description' => $data['description'],
                'meta_keywords' => [
                    'en' => 'real estate, advisory, imas',
                    'ar' => 'عقارات، استشارات، ايماس',
                ],
                'status' => 'Published',
            ], $data));
        }

        $testimonials = [
            [
                'rank' => 10,
                'client' => 'northwind-estates',
                'name' => ['en' => 'Jordan Lee', 'ar' => 'جوردن لي'],
                'position' => ['en' => 'Relocation Lead', 'ar' => 'مسؤول الانتقال'],
                'quote' => [
                    'en' => '<p>They compressed a cross-country move into three focused weekends of tours. Paperwork never stalled.</p>',
                    'ar' => '<p>اختصروا انتقالاً بين الساحلين إلى ثلاثة عطلات نهاية أسبوع للمعاينات. لم تتعطل الأوراق.</p>',
                ],
            ],
            [
                'rank' => 20,
                'client' => 'summit-family-office',
                'name' => ['en' => 'Amelia Ortiz', 'ar' => 'أميليا أورتيز'],
                'position' => ['en' => 'Portfolio Manager', 'ar' => 'مديرة المحفظة'],
                'quote' => [
                    'en' => '<p>Underwriting packets arrived investor-ready. NOI assumptions matched what lenders ultimately approved.</p>',
                    'ar' => '<p>وصلتنا حزم التحليل جاهزة للمستثمر. افتراضات صافي الدخل توافقت مع ما وافق عليه الممولون.</p>',
                ],
            ],
            [
                'rank' => 30,
                'client' => 'bayfront-developers',
                'name' => ['en' => 'Marcus Feld', 'ar' => 'ماركوس فيلد'],
                'position' => ['en' => 'Development Partner', 'ar' => 'شريك تطوير'],
                'quote' => [
                    'en' => '<p>Launch velocity doubled once their marketing team synchronized renders with listing syndication.</p>',
                    'ar' => '<p>تضاعفت سرعة الإطلاق بعد أن زامن فريقهم التصاميم مع نشر الإعلانات.</p>',
                ],
            ],
            [
                'rank' => 40,
                'client' => 'aria-tech-hr',
                'name' => ['en' => 'Priya Shah', 'ar' => 'بريا شاه'],
                'position' => ['en' => 'HR Director', 'ar' => 'مديرة الموارد البشرية'],
                'quote' => [
                    'en' => '<p>New hires received neighborhood packs before day one—huge lift for retention.</p>',
                    'ar' => '<p>حصل الموظفون الجدد على حقائب عن الأحياء قبل اليوم الأول — فرق كبير في الاستبقاء.</p>',
                ],
            ],
            [
                'rank' => 50,
                'client' => 'harbor-construction',
                'name' => ['en' => 'Diego Ramos', 'ar' => 'دييغو راموس'],
                'position' => ['en' => 'Site Superintendent', 'ar' => 'مشرف موقع'],
                'quote' => [
                    'en' => '<p>They flagged zoning quirks early and kept punch-list walkthroughs aligned with lender draws.</p>',
                    'ar' => '<p>أشاروا مبكراً إلى مسائل التخطيط وأبقوا جولات القائمة متوافقة مع صرفيات التمويل.</p>',
                ],
            ],
            [
                'rank' => 60,
                'client' => 'ivy-med-group',
                'name' => ['en' => 'Dr. Hannah Cho', 'ar' => 'د. هانا تشو'],
                'position' => ['en' => 'Clinic Owner', 'ar' => 'مالكة عيادة'],
                'quote' => [
                    'en' => '<p>Found a medical-office condo that satisfied retrofit codes without blowing the capex budget.</p>',
                    'ar' => '<p>عثرنا على وحدة مكتبية طبية تناسب اشتراطات التعديل دون تجاوز ميزانية المصاريف الرأسمالية.</p>',
                ],
            ],
        ];

        foreach ($testimonials as $row) {
            Testimonial::factory()->published()->create($row);
        }

        $teams = [
            ['rank' => 10, 'name' => ['en' => 'Morgan Ellis', 'ar' => 'مورغان إليس'], 'position' => ['en' => 'Managing Broker', 'ar' => 'وسيط إداري']],
            ['rank' => 20, 'name' => ['en' => 'Sara Al-Masri', 'ar' => 'سارة المصري'], 'position' => ['en' => 'Luxury Sales Director', 'ar' => 'مديرة مبيعات فاخرة']],
            ['rank' => 30, 'name' => ['en' => 'Daniel Porter', 'ar' => 'دانيال بورتر'], 'position' => ['en' => 'Investment Analyst', 'ar' => 'محلل استثمارات']],
            ['rank' => 40, 'name' => ['en' => 'Inès Benali', 'ar' => 'إيناس بنعلي'], 'position' => ['en' => 'Marketing Strategist', 'ar' => 'استراتيجية تسويق']],
            ['rank' => 50, 'name' => ['en' => 'Chris Okonkwo', 'ar' => 'كريس أوكونكوو'], 'position' => ['en' => 'Client Experience Lead', 'ar' => 'قائد تجربة العملاء']],
            ['rank' => 60, 'name' => ['en' => 'Yuki Taneda', 'ar' => 'يوكي تانيدا'], 'position' => ['en' => 'Relocation Specialist', 'ar' => 'أخصائي انتقال']],
        ];

        foreach ($teams as $row) {
            Team::factory()->published()->create($row);
        }
    }
}
