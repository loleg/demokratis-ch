<?php

namespace App\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;

class TaggingService
{
    public const TAGS = [
        'energie',
        'bildung',
        'forschung',
        'informatik',
        'landwirtschaft',
        'wirtschaft',
        'finanzen',
        'gesundheit',
        'kultur',
        'verteidigung',
        'sicherheit',
        'recht',
        'verkehr',
        'umwelt',
        'kommunikation',
        'migration',
        'sport',
        'versicherung',
        'raumordnung',
        'diplomatie',
    ];

    private TagRepository $tagRepository;
    public array $tags;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;

        $this->tags = self::TAGS;
    }

    public function getTagEntity(string $tag): Tag|null
    {
        return $this->tagRepository->findOneBy(['slug' => $tag]);
    }

    public function extractTags(string $institution): array|string
    {
        $tags = match ($institution) {
            'Bundesamt für Energie' => ['energie'],
            'Parlamentsdienste' => [],
            'Staatssekretariat für Bildung, Forschung und Innovation' => ['bildung', 'forschung', 'informatik'],
            'Staatssekretariat für Wirtschaft' => ['wirtschaft'],
            'Bundesamt für Gesundheit' => ['gesundheit'],
            'Bundesamt für Kultur' => ['kultur'],
            'Bundesamt für Bevölkerungsschutz' => ['verteidigung', 'sicherheit'],
            'Eidgenössische Steuerverwaltung' => ['finanzen'],
            'Bundesamt für Sozialversicherungen' => ['versicherung'],
            'Bundesamt für Lebensmittelsicherheit und Veterinärwesen' => ['gesundheit', 'verteidigung', 'sicherheit'],
            'Bundesamt für Justiz' => ['recht'],
            'Eidgenössisches Institut für Geistiges Eigentum' => ['recht'],
            'Bundesamt für Verkehr' => ['verkehr'],
            'Generalsekretariat VBS' => ['verteidigung', 'sicherheit', 'sport'],
            'Bundesamt für Strassen' => ['verkehr'],
            'Bundesamt für Raumentwicklung' => ['raumordnung'],
            'Bundesamt für wirtschaftliche Landesversorgung' => ['verteidigung', 'wirtschaft', 'sicherheit'],
            'Bundesamt für Landestopografie' => ['raumordnung'],
            'Bundesamt für Umwelt' => ['umwelt'],
            'Dienst Überwachung Post- und Fernmeldeverkehr' => ['sicherheit', 'verkehr', 'kommunikation'],
            'Nachrichtendienst des Bundes' => ['verteidigung', 'sicherheit', 'diplomatie'],
            'Staatssekretariat für internationale Finanzfragen' => ['wirtschaft', 'finanzen', 'diplomatie'],
            'Bundesamt für Kommunikation' => ['kommunikation'],
            'Generalsekretariat EFD' => ['wirtschaft', 'finanzen'],
            'Staatssekretariat für Migration' => ['migration'],
            'Bundesamt für Polizei' => ['sicherheit'],
            'Eidgenössische Zollverwaltung' => ['sicherheit', 'verkehr', 'migration'],
            'Bundeskanzlei' => [],
            'Bundesamt für Landwirtschaft' => ['landwirtschaft', 'wirtschaft'],
            'Direktion für Völkerrecht' => ['recht', 'diplomatie'],
            'Eidgenössische Finanzverwaltung' => ['wirtschaft', 'finanzen'],
            'Bundesamt für Sport' => ['sport'],
            'Schweizerische Agentur für Innovationsförderung' => ['forschung', 'bildung'],
            'Bundesamt für Zoll und Grenzsicherheit' => ['verteidigung', 'sicherheit', 'verkehr', 'migration'],
            'Staatspolitische Kommission, Sekretariat' => [],
            'Kommission für Wirtschaft und Abgaben' => ['wirtschaft', 'finanzen'],
            'verteidigung' => [],
            'Sicherheitspolitische Kommission' => ['verteidigung', 'sicherheit', 'diplomatie'],
            'Bundesamt für Wohnungswesen' => [],
            'Schweizerische Bundeskanzlei, Sektion Recht, Sekretariat' => ['recht'],
            'Kommission für soziale Sicherheit und Gesundheit' => ['gesundheit', 'sicherheit'],
            'Bundesamt für Zivilluftfahrt' => ['verkehr'],
            'Generalsekretariat UVEK' => ['umwelt', 'verkehr', 'energie', 'kommunikation'],
            'Kommission für Verkehr und Fernmeldewesen' => ['verkehr', 'kommunikation'],
            'Amt für das Handelsregister' => ['wirtschaft'],
            'Eidgenössisches Departement des Innern' => ['gesundheit', 'versicherung', 'kultur', 'umwelt'],
            'Kommission für Rechtsfragen' => ['recht'],
            'Bundesamt für Statistik' => [],
            'Schweizerisches Institut für Rechtsvergleichung' => ['recht'],
            'Bundesamt für Metrologie' => [],
            'Direktion für Entwicklung und Zusammenarbeit' => ['diplomatie'],
            'Kommission für Wissenschaft, Bildung und Kultur' => ['bildung', 'forschung', 'kultur'],
            'Kommission für Wirtschaft und Abgaben des Ständerates' => ['wirtschaft', 'finanzen'],
            'Staatspolitische Kommission des Ständerates' => [],
            'Kommission für Umwelt, Raumplanung und Energie des Nationalrates' => ['umwelt', 'energie', 'raumordnung'],
            'Staatspolitische Kommission des Nationalrates' => [],
            'Bundesamt für Zivildienst' => [],
            'Bundesamt für Meteorologie und Klimatologie' => ['umwelt'],
            'Kommission für Umwelt, Raumplanung und Energie' => ['umwelt', 'raumordnung', 'energie'],
            'Eidgenössisches Departement für Wirtschaft, Bildung und Forschung' => ['wirtschaft', 'bildung', 'forschung'],
            'Schweizerisches Heilmittelinstitut' => ['gesundheit'],
            'Kommission für soziale Sicherheit und Gesundheit des Nationalrates' => ['sicherheit', 'gesundheit'],
            'Eidgenössisches Departement für Verteidigung, Bevölkerungsschutz und Sport' => ['verteidigung', 'sicherheit', 'sport'],
            'Eidgenössisches Departement für Umwelt, Verkehr, Energie und Kommunikation' => ['umwelt', 'verkehr', 'energie', 'kommunikation'],
            'Direktion für Ressourcen' => ['wirtschaft'],
            'Eidgenössisches Finanzdepartement' => ['wirtschaft', 'finanzen'],
            'Kommission für Rechtsfragen des Nationalrates' => ['recht'],
            'Informatik Service Center ISC-EJPD' => ['informatik'],
            'Kommission für Wirtschaft und Abgaben des Nationalrates' => ['wirtschaft', 'finanzen'],
            'Eidgenössisches Amt für das Zivilstandswesen' => [],
            'Eidgenössische Finanzmarktaufsicht' => ['wirtschaft', 'finanzen'],
            'Bundesamt für Bauten und Logistik' => ['wirtschaft'],
            'Kommission für Umwelt, Raumplanung und Energie des Ständerats' => ['umwelt', 'energie', 'raumordnung'],
            'Parlamentsdienste, Sekretariat der Kommissionen für soziale Sicherheit und Gesundheit' => ['sicherheit', 'gesundheit'],
            'Bundesamt für Migration' => ['migration'],
            'Informatiksteuerungsorgan des Bundes' => ['informatik'],
            'Eidgenössische Spielbankenkommission' => ['wirtschaft', 'finanzen'],
            'Eidgenössisches Justiz- und Polizeidepartement' => ['recht', 'sicherheit'],
            'Konsularische Direktion' => ['diplomatie'],
            'Bundesamt für Veterinärwesen' => ['gesundheit'],
            'Oberauditorat' => ['recht', 'verteidigung'],
            'Schweizerische Bundeskanzlei, Sektion Politische Rechte / Projekt Vote électronique' => ['informatik', 'recht'],
            'Bundesamt für Landwirtschaft BLW' => ['landwirtschaft', 'wirtschaft'],
            'Bundeskanzlei, Sektion Politische Rechte' => [],
            'Eidgenössisches Finanzdepartement, Generalsekretariat, Rechtsdienst' => ['wirtschaft', 'recht', 'finanzen'],
            'Kompetenzzentrum Amtliche Veröffentlichungen' => [],
            'Staatssekretariat für Bildung und Forschung' => ['bildung', 'forschung'],
            'Unbekannte Organisationseinheit - Eidgenössisches Finanzdepartement' => ['wirtschaft', 'finanzen'],
            'Bundesamt für Berufsbildung und Technologie' => ['bildung', 'forschung'],
            'Unbekannte Organisationseinheit - Eidgenössisches Justiz- und Polizeidepartement' => ['recht', 'sicherheit'],
            'Unbekannte Organisationseinheit - Eidgenössisches Departement des Innern' => ['gesundheit', 'versicherung', 'kultur', 'umwelt'],
            'Unbekannte Organisationseinheit - Eidgenössisches Departement für Umwelt, Verkehr, Energie und Kommunikation' => ['umwelt', 'verkehr', 'energie', 'kommunikation'],
            'Politische Direktion' => [],
            'Eidgenössisches Hochschulinstitut für Berufsbildung' => ['bildung'],
            'Oberzolldirektion' => ['sicherheit', 'verkehr', 'migration'],
            'SECO, Direktion für Arbeit' => ['wirtschaft', 'diplomatie'],
            'Eidgenössische Alkoholverwaltung' => [],
            'Eidgenössische Finanzkontrolle' => ['wirtschaft', 'finanzen'],
            'Eidgenössische Bankenkommission' => ['wirtschaft', 'finanzen'],
            'Eidgenössisches Personalamt' => [],
            'Unbekannte Organisationseinheit - Eidgenössisches Departement für auswärtige Angelegenheiten' => ['diplomatie'],
            'Schweizerische Bundeskanzlei' => [],
            'Vollzugsstelle für den Zivildienst ZIVI' => [],
            'Bundesanwaltschaft' => ['recht'],
            'Unbekannte Organisationseinheit - Eidgenössisches Volkswirtschaftsdepartement' => ['wirtschaft'],
            'Bundesamt für Umwelt, Wald und Landschaft' => ['umwelt'],
            'Postregulationsbehörde' => ['kommunikation', 'verkehr'],
            'Eidgenössisches Departement für auswärtige Angelegenheiten' => ['diplomatie'],
            'Generalsekretariat VBS, Recht VBS' => ['verteidigung', 'sicherheit', 'sport', 'recht'],
            'Bundesamt für Verkehr, Sektion Recht oder Sektion Güterverkehr' => ['verkehr', 'recht'],
            'Bundesamt für Privatversicherungen' => ['versicherung'],
            'Generalsekretariat EVD' => [],
            'Direktion für Völkerrecht, Sektion Diplomatisches und konsularisches Recht' => ['recht', 'diplomatie'],
            'Bundesamt für Berufsbildung und Technologie, Projektverantwortliche, Ressort Grundsatzfragen und Verfahren' => ['bildung', 'forschung'],
            'Integrationsbüro EDA/EVD' => [],
            'Bundesamt für Zuwanderung, Integration und Auswanderung' => ['migration'],
            'Bundesamt für Bildung und Wissenschaft' => ['bildung', 'forschung'],
            'Bundesamt für Gesundheit, Direktionsbereich Kranken- und Unfallversicherung' => ['gesundheit', 'versicherung'],
            'Kommunikationsdienst EVD' => ['kommunikation'],
            'Unbekannte Organisationseinheit - Eidgenössisches Militärdepartement' => ['verteidigung', 'sicherheit'],
            'Unbekannte Organisationseinheit - Eidgenössisches Verkehrs- und Energiewirtschaftsdepartement' => ['verkehr', 'energie', 'wirtschaft'],
            'Unbekannte Organisationseinheit - Schweizerische Bundeskanzlei' => [],
            'Bundesamt für Flüchtlinge' => ['migration'],
            'Bundesamt für Berufsbildung und Technologie BBT, Ressort höhere Berufsbildung' => ['bildung', 'forschung'],
            'Bundesamt für Wasser und Geologie' => ['umwelt'],
            'Unbekannte Organisationseinheit - Eidgenössisches Departement für Verteidigung, Bevölkerungsschutz und Sport' => ['sicherheit', 'verteidigung', 'sport'],
            default => 'unmatched',
        };

        return $tags;
    }
}
