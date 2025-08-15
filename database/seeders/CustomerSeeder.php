<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customers = [
            [
                'name' => 'Walmart de México',
                'contact_person' => 'Patricia Sánchez',
                'email' => 'psanchez@walmart.com.mx',
                'phone' => '+52 55 5283 0100',
                'address' => 'Av. Acoxpa 430',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'country' => 'México',
                'postal_code' => '14300',
                'customer_type' => 'mayorista',
                'credit_limit' => 500000.00,
                'status' => 'active',
                'notes' => 'Cliente corporativo con compras regulares semanales'
            ],
            [
                'name' => 'Soriana Mercado',
                'contact_person' => 'Jorge Ramírez',
                'email' => 'jramirez@soriana.com',
                'phone' => '+52 81 8329 1800',
                'address' => 'Av. Ricardo Margáin 575',
                'city' => 'Monterrey',
                'state' => 'Nuevo León',
                'country' => 'México',
                'postal_code' => '66269',
                'customer_type' => 'mayorista',
                'credit_limit' => 300000.00,
                'status' => 'active',
                'notes' => 'Cadena de supermercados con 20+ sucursales'
            ],
            [
                'name' => 'Fresh Produce USA Inc.',
                'contact_person' => 'John Smith',
                'email' => 'jsmith@freshproduceusa.com',
                'phone' => '+1 213 555 0123',
                'address' => '1245 Wholesale District',
                'city' => 'Los Angeles',
                'state' => 'California',
                'country' => 'Estados Unidos',
                'postal_code' => '90021',
                'customer_type' => 'distribuidor',
                'credit_limit' => 750000.00,
                'status' => 'active',
                'notes' => 'Distribuidor internacional especializado en aguacate mexicano'
            ],
            [
                'name' => 'Mercado Central de Guadalajara',
                'contact_person' => 'Elena Morales',
                'email' => 'emorales@mercadocentral.com',
                'phone' => '+52 33 3613 7500',
                'address' => 'Av. Dr. R. Michel 1355',
                'city' => 'Guadalajara',
                'state' => 'Jalisco',
                'country' => 'México',
                'postal_code' => '44200',
                'customer_type' => 'mayorista',
                'credit_limit' => 150000.00,
                'status' => 'active',
                'notes' => 'Mayorista regional con 15 años de relación comercial'
            ],
            [
                'name' => 'Restaurante El Aguacate Dorado',
                'contact_person' => 'Chef Marco Rodríguez',
                'email' => 'marco@aguacatedorado.com',
                'phone' => '+52 55 5574 8899',
                'address' => 'Av. Insurgentes Sur 1235',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'country' => 'México',
                'postal_code' => '03100',
                'customer_type' => 'minorista',
                'credit_limit' => 25000.00,
                'status' => 'active',
                'notes' => 'Restaurante especializado en platillos con aguacate'
            ],
            [
                'name' => 'Exportadora Tokyo Fresh',
                'contact_person' => 'Hiroshi Tanaka',
                'email' => 'htanaka@tokyofresh.jp',
                'phone' => '+81 3 5555 1234',
                'address' => '2-3-1 Tsukiji, Chuo-ku',
                'city' => 'Tokyo',
                'state' => 'Tokyo',
                'country' => 'Japón',
                'postal_code' => '104-0045',
                'customer_type' => 'distribuidor',
                'credit_limit' => 1000000.00,
                'status' => 'active',
                'notes' => 'Importador japonés premium con estrictos estándares de calidad'
            ]
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
