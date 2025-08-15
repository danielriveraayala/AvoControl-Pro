<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $suppliers = [
            [
                'name' => 'Productores Unidos de Michoacán',
                'contact_person' => 'Luis González',
                'email' => 'lgonzalez@pumichoacan.com',
                'phone' => '+52 443 315 2847',
                'address' => 'Av. Lázaro Cárdenas 1540',
                'city' => 'Uruapan',
                'state' => 'Michoacán',
                'country' => 'México',
                'postal_code' => '60000',
                'status' => 'active',
                'notes' => 'Proveedor principal de aguacate Hass de exportación'
            ],
            [
                'name' => 'Aguacates de Jalisco SA',
                'contact_person' => 'Ana Martínez',
                'email' => 'amartinez@aguacatesjalisco.com',
                'phone' => '+52 33 3652 1489',
                'address' => 'Carretera Guadalajara-Chapala Km 15',
                'city' => 'Tlajomulco de Zúñiga',
                'state' => 'Jalisco',
                'country' => 'México',
                'postal_code' => '45640',
                'status' => 'active',
                'notes' => 'Especialistas en aguacate orgánico certificado'
            ],
            [
                'name' => 'Exportadora Los Altos',
                'contact_person' => 'Roberto Hernández',
                'email' => 'rhernandez@losaltos.mx',
                'phone' => '+52 378 782 5963',
                'address' => 'Zona Industrial Norte s/n',
                'city' => 'Tancítaro',
                'state' => 'Michoacán',
                'country' => 'México',
                'postal_code' => '60290',
                'status' => 'active',
                'notes' => 'Proveedor con certificación GlobalGAP'
            ],
            [
                'name' => 'Huertos San Pedro',
                'contact_person' => 'Carmen López',
                'email' => 'clopez@huertossanpedro.com',
                'phone' => '+52 461 614 7823',
                'address' => 'Rancho Los Pinos s/n',
                'city' => 'Peribán',
                'state' => 'Michoacán',
                'country' => 'México',
                'postal_code' => '60440',
                'status' => 'active',
                'notes' => 'Productor familiar con 20 años de experiencia'
            ],
            [
                'name' => 'Agrícola Valle Verde',
                'contact_person' => 'Miguel Torres',
                'email' => 'mtorres@valleverde.com.mx',
                'phone' => '+52 351 515 8741',
                'address' => 'Ejido La Cantera',
                'city' => 'Los Reyes',
                'state' => 'Michoacán',
                'country' => 'México',
                'postal_code' => '60300',
                'status' => 'active',
                'notes' => 'Proveedor de aguacate premium para mercado asiático'
            ]
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
