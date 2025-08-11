<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class CompanySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companySettings = [
            'company_name' => 'AvoControl-Pro',
            'company_rfc' => '',
            'company_address' => 'Uruapan, Michoacán, México',
            'company_city' => 'Uruapan',
            'company_state' => 'Michoacán',
            'company_postal_code' => '60000',
            'company_phone' => '',
            'company_email' => '',
            'company_website' => '',
            'company_logo' => '',
            'company_description' => 'Sistema de control y gestión de acopio de aguacate para Centros de Acopio.',
        ];

        foreach ($companySettings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'company'],
                ['value' => $value, 'description' => $this->getDescription($key)]
            );
        }
    }

    private function getDescription($field)
    {
        $descriptions = [
            'company_name' => 'Nombre de la empresa',
            'company_rfc' => 'RFC de la empresa',
            'company_address' => 'Dirección de la empresa',
            'company_city' => 'Ciudad donde se ubica la empresa',
            'company_state' => 'Estado donde se ubica la empresa',
            'company_postal_code' => 'Código postal de la empresa',
            'company_phone' => 'Teléfono de contacto de la empresa',
            'company_email' => 'Email de contacto de la empresa',
            'company_website' => 'Sitio web de la empresa',
            'company_logo' => 'URL del logo de la empresa',
            'company_description' => 'Descripción de la empresa para reportes'
        ];

        return $descriptions[$field] ?? '';
    }
}
