<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Enums;

enum NounFormEnum: string
{
    /*
    |--------------------------------------------------------------------------
    | Active Participles - أسماء الفاعل
    |--------------------------------------------------------------------------
    */

    case ActiveParticipleFaael = 'active_participle_faael'; // فاعل
    case ActiveParticipleMufail = 'active_participle_mufail'; // مُفعل / مُفعِل
    case ActiveParticipleMustafil = 'active_participle_mustafil'; // مستفعل
    case ActiveParticipleMutafael = 'active_participle_mutafael'; // متفعّل
    case ActiveParticipleMutafaael = 'active_participle_mutafaael'; // متفاعل
    case ActiveParticipleMunfail = 'active_participle_munfail'; // منفعل
    case ActiveParticipleMuftail = 'active_participle_muftail'; // مفتعل

    /*
    |--------------------------------------------------------------------------
    | Passive Participles - أسماء المفعول
    |--------------------------------------------------------------------------
    */

    case PassiveParticipleMafool = 'passive_participle_mafool'; // مفعول
    case PassiveParticipleMufa33al = 'passive_participle_mufa33al'; // مفعّل
    case PassiveParticipleMufaal = 'passive_participle_mufaal'; // مفاعل / مُفاعَل
    case PassiveParticipleMuftaal = 'passive_participle_muftaal'; // مفتعل / مُفتعَل
    case PassiveParticipleMunfaal = 'passive_participle_munfaal'; // منفعل / مُنفعَل
    case PassiveParticipleMustafaal = 'passive_participle_mustafaal'; // مستفعل / مُستفعَل

    /*
    |--------------------------------------------------------------------------
    | Instrument Nouns - أسماء الآلة
    |--------------------------------------------------------------------------
    */

    case InstrumentMifal = 'instrument_mifal'; // مفعال
    case InstrumentMifaal = 'instrument_mifaal'; // مفعال - long alif spelling
    case InstrumentMifala = 'instrument_mifala'; // مفعلة
    case InstrumentMifaalah = 'instrument_mifaalah'; // مفعالة
    case InstrumentMifalModern = 'instrument_mifal_modern'; // مِفعل / modern ambiguous
    case InstrumentFaala = 'instrument_faala'; // فعالة
    case InstrumentFaaool = 'instrument_faaool'; // فاعول

    /*
    |--------------------------------------------------------------------------
    | Place / Time Nouns - أسماء المكان والزمان
    |--------------------------------------------------------------------------
    */

    case PlaceTimeMafal = 'place_time_mafal'; // مفعل
    case PlaceTimeMafil = 'place_time_mafil'; // مفعل / مفعل-like unvocalized
    case PlaceTimeMafala = 'place_time_mafala'; // مفعلة
    case PlaceTimeMafila = 'place_time_mafila'; // مفعلة / مفعلة-like

    /*
    |--------------------------------------------------------------------------
    | Verbal Nouns / Masdars - المصادر
    |--------------------------------------------------------------------------
    |
    | Keep masdar forms here because your root rules already treat many noun
    | patterns as masdar-derived root evidence.
    |
    */

    case MasdarTriliteralFaal = 'masdar_triliteral_faal'; // فعل
    case MasdarTriliteralFiaal = 'masdar_triliteral_fiaal'; // فعال
    case MasdarTriliteralFuool = 'masdar_triliteral_fuool'; // فعول
    case MasdarTriliteralFalaan = 'masdar_triliteral_falaan'; // فعلان
    case MasdarTriliteralFi3ala = 'masdar_triliteral_fi3ala'; // فعالة
    case MasdarTriliteralFiaalat = 'masdar_triliteral_fiaalat'; // فعالة / فيالة-like
    case MasdarTriliteralTafeel = 'masdar_triliteral_tafeel'; // تفعيل
    case MasdarTriliteralIfaal = 'masdar_triliteral_ifaal'; // إفعال
    case MasdarTriliteralMufaala = 'masdar_triliteral_mufaala'; // مفاعلة
    case MasdarTriliteralTafaul = 'masdar_triliteral_tafaul'; // تفاعل
    case MasdarTriliteralIftial = 'masdar_triliteral_iftial'; // افتعال
    case MasdarTriliteralInfaal = 'masdar_triliteral_infaal'; // انفعال
    case MasdarTriliteralIstifal = 'masdar_triliteral_istifal'; // استفعال

    case MasdarQuadriliteralFalala = 'masdar_quadriliteral_falala'; // فعللة
    case MasdarQuadriliteralTafalul = 'masdar_quadriliteral_tafalul'; // تفعلل
    case MasdarQuadriliteralFi3lal = 'masdar_quadriliteral_fi3lal'; // فعلال

    case MasdarQuinqueliteralInfaal = 'masdar_quinqueliteral_infaal'; // انفعال
    case MasdarQuinqueliteralIftial = 'masdar_quinqueliteral_iftial'; // افتعال
    case MasdarQuinqueliteralIfilal = 'masdar_quinqueliteral_ifilal'; // افعلال
    case MasdarQuinqueliteralTafaul = 'masdar_quinqueliteral_tafaul'; // تفعّل / تفاعل family

    case MasdarSextiliteralIstifal = 'masdar_sextiliteral_istifal'; // استفعال

    /*
    |--------------------------------------------------------------------------
    | Adjectival / Derived Noun Forms - صفات وأسماء مشتقة
    |--------------------------------------------------------------------------
    */

    case Nisba = 'nisba'; // مصري، عباسي
    case ComparativeAfal = 'comparative_afal'; // أكبر، أفضل
    case ColorDefectAfal = 'color_defect_afal'; // أحمر، أعرج
    case IntensiveFaaal = 'intensive_faaal'; // فعّال
    case IntensiveFaool = 'intensive_faool'; // فعول
    case IntensiveFaeel = 'intensive_faeel'; // فعيل
    case IntensiveMifaal = 'intensive_mifaal'; // مفعال

    /*
    |--------------------------------------------------------------------------
    | Plural / Lexical Noun Forms - جموع وأبنية اسمية
    |--------------------------------------------------------------------------
    |
    | These should usually be non-authoritative without manual/database evidence.
    |
    */

    case BrokenPluralAfal = 'broken_plural_afal'; // أفعال
    case BrokenPluralFuul = 'broken_plural_fuul'; // فعول
    case BrokenPluralFiaal = 'broken_plural_fiaal'; // فعال
    case BrokenPluralFuaal = 'broken_plural_fuaal'; // فُعال
    case BrokenPluralMafail = 'broken_plural_mafail'; // مفاعل
    case BrokenPluralMafaail = 'broken_plural_mafaail'; // مفاعيل
    case BrokenPluralFawail = 'broken_plural_fawail'; // فواعل
    case BrokenPluralFaeail = 'broken_plural_faeail'; // فعائل

    /*
    |--------------------------------------------------------------------------
    | Fallback / Ambiguous
    |--------------------------------------------------------------------------
    */

    case AmbiguousNounPattern = 'ambiguous_noun_pattern';
}
