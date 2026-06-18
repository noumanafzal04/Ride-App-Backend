<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;

class PakPunjabCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Punjab tehsils into cities table...');

        $tehsils = [
            // ── Bahawalpur Division ──────────────────────────────────────────
            // Bahawalnagar District
            ['name' => 'Bahawalnagar',          'lat' => 29.9937, 'lon' => 73.2545],
            ['name' => 'Chishtian',             'lat' => 29.7975, 'lon' => 72.8567],
            ['name' => 'Haroonabad',            'lat' => 29.6139, 'lon' => 73.1314],
            ['name' => 'Minchinabad',           'lat' => 30.1647, 'lon' => 73.5694],
            ['name' => 'Fort Abbas',            'lat' => 29.1928, 'lon' => 72.8547],

            // Bahawalpur District
            ['name' => 'Ahmadpur East',         'lat' => 29.1436, 'lon' => 71.2572],
            ['name' => 'Bahawalpur City',       'lat' => 29.3957, 'lon' => 71.6833],
            ['name' => 'Bahawalpur Saddar',     'lat' => 29.3700, 'lon' => 71.6700],
            ['name' => 'Hasilpur',              'lat' => 29.6939, 'lon' => 72.5550],
            ['name' => 'Khairpur Tamewali',     'lat' => 29.5614, 'lon' => 72.2228],
            ['name' => 'Yazman',                'lat' => 28.9197, 'lon' => 71.7444],

            // Rahim Yar Khan District
            ['name' => 'Rahim Yar Khan',        'lat' => 28.4201, 'lon' => 70.2959],
            ['name' => 'Sadiqabad',             'lat' => 28.3104, 'lon' => 70.1274],
            ['name' => 'Liaqatpur',             'lat' => 28.9233, 'lon' => 70.9058],
            ['name' => 'Khanpur',               'lat' => 28.6469, 'lon' => 70.6571],

            // ── D.G. Khan Division ───────────────────────────────────────────
            // Dera Ghazi Khan District
            ['name' => 'Dera Ghazi Khan',       'lat' => 30.0325, 'lon' => 70.6402],
            ['name' => 'Kot Chutta',            'lat' => 30.4667, 'lon' => 70.5333],

            // Jampur District
            ['name' => 'Jampur',                'lat' => 29.6431, 'lon' => 70.5958],
            ['name' => 'Dajal',                 'lat' => 29.5500, 'lon' => 70.4333],

            // Kot Addu District
            ['name' => 'Kot Addu',              'lat' => 30.4667, 'lon' => 70.9667],
            ['name' => 'Chowk Sarwar Shaheed',  'lat' => 30.5667, 'lon' => 71.0167],

            // Layyah District
            ['name' => 'Layyah',                'lat' => 30.9648, 'lon' => 70.9399],
            ['name' => 'Karor Lal Esan',        'lat' => 31.2242, 'lon' => 70.9519],
            ['name' => 'Chaubara',              'lat' => 30.7667, 'lon' => 71.0333],

            // Muzaffargarh District
            ['name' => 'Muzaffargarh',          'lat' => 30.0744, 'lon' => 71.1847],
            ['name' => 'Jatoi',                 'lat' => 29.9900, 'lon' => 70.8500],
            ['name' => 'Alipur',                'lat' => 29.3667, 'lon' => 70.9167],

            // Rajanpur District
            ['name' => 'Rajanpur',              'lat' => 29.1042, 'lon' => 70.3300],
            ['name' => 'Rojhan',                'lat' => 28.6886, 'lon' => 69.9667],

            // Taunsa District
            ['name' => 'Taunsa',                'lat' => 30.7056, 'lon' => 70.6578],

            // ── Faisalabad Division ──────────────────────────────────────────
            // Chiniot District
            ['name' => 'Chiniot',               'lat' => 31.7200, 'lon' => 72.9781],
            ['name' => 'Bhowana',               'lat' => 31.5597, 'lon' => 72.6503],
            ['name' => 'Lalian',                'lat' => 31.8297, 'lon' => 72.8006],

            // Faisalabad District
            ['name' => 'Faisalabad City',       'lat' => 31.4187, 'lon' => 73.0791],
            ['name' => 'Faisalabad Saddar',     'lat' => 31.3956, 'lon' => 73.0650],
            ['name' => 'Chak Jhumra',           'lat' => 31.5667, 'lon' => 73.1833],
            ['name' => 'Jaranwala',             'lat' => 31.3454, 'lon' => 73.4298],
            ['name' => 'Samundri',              'lat' => 31.0644, 'lon' => 72.9678],
            ['name' => 'Tandlianwala',          'lat' => 31.0333, 'lon' => 73.1333],

            // Jhang District
            ['name' => 'Jhang',                 'lat' => 31.2780, 'lon' => 72.3118],
            ['name' => 'Shorkot',               'lat' => 30.9667, 'lon' => 72.0000],
            ['name' => 'Ahmadpur Sial',         'lat' => 31.2500, 'lon' => 71.7833],
            ['name' => 'Athara Hazari',         'lat' => 31.5000, 'lon' => 72.0167],

            // Toba Tek Singh District
            ['name' => 'Toba Tek Singh',        'lat' => 30.9769, 'lon' => 72.4843],
            ['name' => 'Kamalia',               'lat' => 30.7256, 'lon' => 72.6454],

            // ── Gujranwala Division ──────────────────────────────────────────
            // Gujranwala District
            ['name' => 'Gujranwala',            'lat' => 32.1617, 'lon' => 74.1883],
            ['name' => 'Kamoke',                'lat' => 31.9747, 'lon' => 74.2214],
            ['name' => 'Nowshera Virkan',       'lat' => 32.0333, 'lon' => 73.8667],
            ['name' => 'Wazirabad',             'lat' => 32.4436, 'lon' => 74.1203],

            // Gujrat District
            ['name' => 'Gujrat',                'lat' => 32.5742, 'lon' => 74.0797],
            ['name' => 'Kharian',               'lat' => 32.8167, 'lon' => 73.8833],
            ['name' => 'Sarai Alamgir',         'lat' => 32.9031, 'lon' => 73.7572],

            // Hafizabad District
            ['name' => 'Hafizabad',             'lat' => 32.0711, 'lon' => 73.6878],
            ['name' => 'Pindi Bhattian',        'lat' => 31.9000, 'lon' => 73.2667],

            // Mandi Bahauddin District
            ['name' => 'Mandi Bahauddin',       'lat' => 32.5864, 'lon' => 73.4917],
            ['name' => 'Malikwal',              'lat' => 32.3478, 'lon' => 73.8917],
            ['name' => 'Phalia',                'lat' => 32.4333, 'lon' => 73.5833],

            // Narowal District
            ['name' => 'Narowal',               'lat' => 32.1000, 'lon' => 74.8833],
            ['name' => 'Shakargarh',            'lat' => 32.2639, 'lon' => 75.1600],

            // Sialkot District
            ['name' => 'Sialkot',               'lat' => 32.4945, 'lon' => 74.5229],
            ['name' => 'Daska',                 'lat' => 32.3247, 'lon' => 74.3503],
            ['name' => 'Pasrur',                'lat' => 32.2667, 'lon' => 74.6667],
            ['name' => 'Sambrial',              'lat' => 32.4736, 'lon' => 74.3525],

            // ── Lahore Division ──────────────────────────────────────────────
            // Lahore District
            ['name' => 'Lahore',                'lat' => 31.5497, 'lon' => 74.3436],
            ['name' => 'Lahore Cantt',          'lat' => 31.5200, 'lon' => 74.3800],
            ['name' => 'Model Town',            'lat' => 31.4833, 'lon' => 74.3167],
            ['name' => 'Raiwind',               'lat' => 31.2489, 'lon' => 74.2258],
            ['name' => 'Shalimar',              'lat' => 31.5667, 'lon' => 74.3833],

            // Kasur District
            ['name' => 'Kasur',                 'lat' => 31.1167, 'lon' => 74.4500],
            ['name' => 'Chunian',               'lat' => 30.9667, 'lon' => 73.9667],
            ['name' => 'Pattoki',               'lat' => 31.0208, 'lon' => 73.8567],

            // Nankana Sahib District
            ['name' => 'Nankana Sahib',         'lat' => 31.4500, 'lon' => 73.7167],
            ['name' => 'Sangla Hill',           'lat' => 31.7153, 'lon' => 73.3817],
            ['name' => 'Shahkot',               'lat' => 31.5500, 'lon' => 73.4500],

            // Sheikhupura District
            ['name' => 'Sheikhupura',           'lat' => 31.7131, 'lon' => 73.9850],
            ['name' => 'Ferozewala',            'lat' => 31.7667, 'lon' => 74.1667],
            ['name' => 'Muridke',               'lat' => 31.8022, 'lon' => 74.2606],

            // ── Multan Division ──────────────────────────────────────────────
            // Khanewal District
            ['name' => 'Khanewal',              'lat' => 30.3019, 'lon' => 71.9319],
            ['name' => 'Jahanian',              'lat' => 30.2833, 'lon' => 72.2833],
            ['name' => 'Mian Channu',           'lat' => 30.4400, 'lon' => 72.3533],

            // Lodhran District
            ['name' => 'Lodhran',               'lat' => 29.5333, 'lon' => 71.6333],
            ['name' => 'Dunyapur',              'lat' => 29.7956, 'lon' => 71.7181],
            ['name' => 'Kehror Pakka',          'lat' => 29.3667, 'lon' => 71.9167],

            // Multan District
            ['name' => 'Multan City',           'lat' => 30.1978, 'lon' => 71.4711],
            ['name' => 'Multan Saddar',         'lat' => 30.1760, 'lon' => 71.4720],
            ['name' => 'Shujabad',              'lat' => 29.8778, 'lon' => 71.3050],

            // Vehari District
            ['name' => 'Vehari',                'lat' => 30.0444, 'lon' => 72.3494],
            ['name' => 'Burewala',              'lat' => 30.1667, 'lon' => 72.6667],
            ['name' => 'Mailsi',                'lat' => 29.8000, 'lon' => 72.1667],

            // ── Rawalpindi Division ──────────────────────────────────────────
            // Attock District
            ['name' => 'Attock',                'lat' => 33.7681, 'lon' => 72.3607],
            ['name' => 'Fateh Jang',            'lat' => 33.5617, 'lon' => 72.6503],
            ['name' => 'Hassan Abdal',          'lat' => 33.8197, 'lon' => 72.6889],
            ['name' => 'Hazro',                 'lat' => 33.9050, 'lon' => 72.4917],
            ['name' => 'Jand',                  'lat' => 33.4333, 'lon' => 72.0167],
            ['name' => 'Pindi Gheb',            'lat' => 33.2500, 'lon' => 72.2667],

            // Chakwal District
            ['name' => 'Chakwal',               'lat' => 32.9325, 'lon' => 72.8555],
            ['name' => 'Choa Saidan Shah',      'lat' => 32.7200, 'lon' => 72.9833],
            ['name' => 'Kallar Kahar',          'lat' => 32.7769, 'lon' => 72.7003],

            // Jhelum District
            ['name' => 'Jhelum',                'lat' => 32.9405, 'lon' => 73.7276],
            ['name' => 'Dina',                  'lat' => 32.7667, 'lon' => 73.5167],
            ['name' => 'Pind Dadan Khan',       'lat' => 32.5833, 'lon' => 73.0333],
            ['name' => 'Sohawa',                'lat' => 33.0667, 'lon' => 73.5333],

            // Murree District
            ['name' => 'Murree',                'lat' => 33.9042, 'lon' => 73.3903],
            ['name' => 'Kotli Sattian',         'lat' => 33.6667, 'lon' => 73.3833],

            // Rawalpindi District
            ['name' => 'Rawalpindi',            'lat' => 33.6261, 'lon' => 73.0714],
            ['name' => 'Gujar Khan',            'lat' => 33.2553, 'lon' => 73.3039],
            ['name' => 'Kahuta',                'lat' => 33.5950, 'lon' => 73.3900],
            ['name' => 'Kallar Syedan',         'lat' => 33.7833, 'lon' => 73.2833],
            ['name' => 'Taxila',                'lat' => 33.7464, 'lon' => 72.8419],

            // Talagang District
            ['name' => 'Talagang',              'lat' => 32.9283, 'lon' => 72.4156],
            ['name' => 'Lawa',                  'lat' => 33.1000, 'lon' => 72.3667],

            // ── Sahiwal Division ─────────────────────────────────────────────
            // Okara District
            ['name' => 'Okara',                 'lat' => 30.8085, 'lon' => 73.4594],
            ['name' => 'Depalpur',              'lat' => 30.7111, 'lon' => 73.1381],
            ['name' => 'Renala Khurd',          'lat' => 30.8789, 'lon' => 73.5981],

            // Pakpattan District
            ['name' => 'Pakpattan',             'lat' => 30.3427, 'lon' => 73.3869],
            ['name' => 'Arifwala',              'lat' => 30.2979, 'lon' => 73.0582],

            // Sahiwal District
            ['name' => 'Sahiwal',               'lat' => 30.6777, 'lon' => 73.1068],
            ['name' => 'Chichawatni',           'lat' => 30.5351, 'lon' => 72.6995],

            // ── Sargodha Division ────────────────────────────────────────────
            // Bhakkar District
            ['name' => 'Bhakkar',               'lat' => 31.6333, 'lon' => 71.0667],
            ['name' => 'Darya Khan',            'lat' => 31.7928, 'lon' => 71.1019],
            ['name' => 'Kaloorkot',             'lat' => 31.4833, 'lon' => 71.2500],
            ['name' => 'Mankera',               'lat' => 31.3833, 'lon' => 71.4333],

            // Khushab District
            ['name' => 'Khushab',               'lat' => 32.2944, 'lon' => 72.3497],
            ['name' => 'Noorpur Thal',          'lat' => 32.1000, 'lon' => 71.9000],
            ['name' => 'Quaidabad',             'lat' => 32.3333, 'lon' => 72.0667],

            // Mianwali District
            ['name' => 'Mianwali',              'lat' => 32.5838, 'lon' => 71.5436],
            ['name' => 'Isakhel',               'lat' => 32.8511, 'lon' => 71.9942],
            ['name' => 'Piplan',                'lat' => 32.2000, 'lon' => 71.3500],

            // Sargodha District
            ['name' => 'Sargodha',              'lat' => 32.0825, 'lon' => 72.6691],
            ['name' => 'Bhalwal',               'lat' => 32.2654, 'lon' => 72.9054],
            ['name' => 'Bhera',                 'lat' => 32.4764, 'lon' => 72.9064],
            ['name' => 'Kot Momin',             'lat' => 32.1833, 'lon' => 72.8833],
            ['name' => 'Sahiwal (Sargodha)',    'lat' => 31.7667, 'lon' => 72.3667],
            ['name' => 'Shahpur',               'lat' => 32.2866, 'lon' => 72.4303],
            ['name' => 'Sillanwali',            'lat' => 32.0167, 'lon' => 72.4500],
        ];

        $saved = 0;

        foreach ($tehsils as $tehsil) {
            City::create([
                'name'     => $tehsil['name'],
                'province' => 'Punjab',
                'lat'      => $tehsil['lat'],
                'lon'      => $tehsil['lon'],
            ]);

            $saved++;
            $this->command->line("  ✓ [{$saved}] {$tehsil['name']} (lat:{$tehsil['lat']}, lon:{$tehsil['lon']})");
        }

        $this->command->info("✅ Done — {$saved} cities inserted.");
    }
}
