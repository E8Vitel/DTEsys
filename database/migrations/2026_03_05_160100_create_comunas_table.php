<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regiones')->cascadeOnDelete();
            $table->string('nombre');
            $table->timestamps();

            $table->unique(['region_id', 'nombre']);
        });

        $regiones = DB::table('regiones')->pluck('id', 'codigo')->toArray();
        $now = now();

        $comunasPorRegion = [
            '15' => ['Arica', 'Camarones', 'Putre', 'General Lagos'],
            '01' => ['Iquique', 'Alto Hospicio', 'Pozo Almonte', 'Camina', 'Colchane', 'Huara', 'Pica'],
            '02' => ['Antofagasta', 'Mejillones', 'Sierra Gorda', 'Taltal', 'Calama', 'Ollague', 'San Pedro de Atacama', 'Tocopilla', 'Maria Elena'],
            '03' => ['Copiapo', 'Caldera', 'Tierra Amarilla', 'Chanaral', 'Diego de Almagro', 'Vallenar', 'Alto del Carmen', 'Freirina', 'Huasco'],
            '04' => ['La Serena', 'Coquimbo', 'Andacollo', 'La Higuera', 'Paihuano', 'Vicuna', 'Illapel', 'Canela', 'Los Vilos', 'Salamanca', 'Ovalle', 'Combarbala', 'Monte Patria', 'Punitaqui', 'Rio Hurtado'],
            '05' => ['Valparaiso', 'Casablanca', 'Concon', 'Juan Fernandez', 'Puchuncavi', 'Quintero', 'Vina del Mar', 'Isla de Pascua', 'Los Andes', 'Calle Larga', 'Rinconada', 'San Esteban', 'La Ligua', 'Cabildo', 'Papudo', 'Petorca', 'Zapallar', 'Quillota', 'La Calera', 'Hijuelas', 'La Cruz', 'Nogales', 'San Antonio', 'Algarrobo', 'Cartagena', 'El Quisco', 'El Tabo', 'Santo Domingo', 'San Felipe', 'Catemu', 'Llaillay', 'Panquehue', 'Putaendo', 'Santa Maria', 'Quilpue', 'Limache', 'Olmue', 'Villa Alemana'],
            '13' => ['Cerrillos', 'Cerro Navia', 'Conchali', 'El Bosque', 'Estacion Central', 'Huechuraba', 'Independencia', 'La Cisterna', 'La Florida', 'La Granja', 'La Pintana', 'La Reina', 'Las Condes', 'Lo Barnechea', 'Lo Espejo', 'Lo Prado', 'Macul', 'Maipu', 'Nunoa', 'Pedro Aguirre Cerda', 'Penalolen', 'Providencia', 'Pudahuel', 'Quilicura', 'Quinta Normal', 'Recoleta', 'Renca', 'San Joaquin', 'San Miguel', 'San Ramon', 'Santiago', 'Vitacura', 'Puente Alto', 'Pirque', 'San Jose de Maipo', 'Colina', 'Lampa', 'Tiltil', 'San Bernardo', 'Buin', 'Calera de Tango', 'Paine', 'Melipilla', 'Alhue', 'Curacavi', 'Maria Pinto', 'San Pedro', 'Talagante', 'El Monte', 'Isla de Maipo', 'Padre Hurtado', 'Penaflor'],
            '06' => ['Rancagua', 'Codegua', 'Coinco', 'Coltauco', 'Donihue', 'Graneros', 'Las Cabras', 'Machali', 'Malloa', 'Mostazal', 'Olivar', 'Peumo', 'Pichidegua', 'Quinta de Tilcoco', 'Rengo', 'Requinoa', 'San Vicente', 'Pichilemu', 'La Estrella', 'Litueche', 'Marchigue', 'Navidad', 'Paredones', 'San Fernando', 'Chepica', 'Chimbarongo', 'Lolol', 'Nancagua', 'Palmilla', 'Peralillo', 'Placilla', 'Pumanque', 'Santa Cruz'],
            '07' => ['Talca', 'Constitucion', 'Curepto', 'Empedrado', 'Maule', 'Pelarco', 'Pencahue', 'Rio Claro', 'San Clemente', 'San Rafael', 'Cauquenes', 'Chanco', 'Pelluhue', 'Curico', 'Hualane', 'Licanten', 'Molina', 'Rauco', 'Romeral', 'Sagrada Familia', 'Teno', 'Vichuquen', 'Linares', 'Colbun', 'Longavi', 'Parral', 'Retiro', 'San Javier', 'Villa Alegre', 'Yerbas Buenas'],
            '16' => ['Chillan', 'Bulnes', 'Chillan Viejo', 'El Carmen', 'Pemuco', 'Pinto', 'Quillon', 'San Ignacio', 'Yungay', 'Cobquecura', 'Coelemu', 'Ninhue', 'Portezuelo', 'Quirihue', 'Ranquil', 'Trehuaco', 'San Carlos', 'Coihueco', 'Niquen', 'San Fabian', 'San Nicolas'],
            '08' => ['Concepcion', 'Coronel', 'Chiguayante', 'Florida', 'Hualqui', 'Lota', 'Penco', 'San Pedro de la Paz', 'Santa Juana', 'Talcahuano', 'Tome', 'Hualpen', 'Lebu', 'Arauco', 'Canete', 'Contulmo', 'Curanilahue', 'Los Alamos', 'Tirua', 'Los Angeles', 'Antuco', 'Cabrero', 'Laja', 'Mulchen', 'Nacimiento', 'Negrete', 'Quilaco', 'Quilleco', 'San Rosendo', 'Santa Barbara', 'Tucapel', 'Yumbel', 'Alto Biobio'],
            '09' => ['Temuco', 'Carahue', 'Cunco', 'Curarrehue', 'Freire', 'Galvarino', 'Gorbea', 'Lautaro', 'Loncoche', 'Melipeuco', 'Nueva Imperial', 'Padre Las Casas', 'Perquenco', 'Pitrufquen', 'Pucon', 'Saavedra', 'Teodoro Schmidt', 'Tolten', 'Vilcun', 'Villarrica', 'Cholchol', 'Angol', 'Collipulli', 'Curacautin', 'Ercilla', 'Lonquimay', 'Los Sauces', 'Lumaco', 'Puren', 'Renaico', 'Traiguen', 'Victoria'],
            '14' => ['Valdivia', 'Corral', 'Lanco', 'Los Lagos', 'Mafil', 'Mariquina', 'Paillaco', 'Panguipulli', 'La Union', 'Futrono', 'Lago Ranco', 'Rio Bueno'],
            '10' => ['Puerto Montt', 'Calbuco', 'Cochamo', 'Fresia', 'Frutillar', 'Los Muermos', 'Llanquihue', 'Maullin', 'Puerto Varas', 'Castro', 'Ancud', 'Chonchi', 'Curaco de Velez', 'Dalcahue', 'Puqueldon', 'Queilen', 'Quellon', 'Quemchi', 'Quinchao', 'Osorno', 'Puerto Octay', 'Purranque', 'Puyehue', 'Rio Negro', 'San Juan de la Costa', 'San Pablo', 'Chaiten', 'Futaleufu', 'Hualaihue', 'Palena'],
            '11' => ['Coyhaique', 'Lago Verde', 'Aisen', 'Cisnes', 'Guaitecas', 'Cochrane', 'OHiggins', 'Tortel', 'Chile Chico', 'Rio Ibanez'],
            '12' => ['Punta Arenas', 'Laguna Blanca', 'Rio Verde', 'San Gregorio', 'Cabo de Hornos', 'Antartica', 'Porvenir', 'Primavera', 'Timaukel', 'Natales', 'Torres del Paine'],
        ];

        $rows = [];

        foreach ($comunasPorRegion as $codigoRegion => $comunas) {
            if (!isset($regiones[$codigoRegion])) {
                continue;
            }

            $regionId = $regiones[$codigoRegion];

            foreach ($comunas as $comuna) {
                $rows[] = [
                    'region_id' => $regionId,
                    'nombre' => $comuna,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('comunas')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comunas');
    }
};
