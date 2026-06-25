<?php

namespace App\Services;

class Icd10Service
{
    /**
     * Map of common ICD-10 codes to descriptions in English and Thai.
     */
    protected static $descriptions = [
        'R99' => [
            'en' => 'Other ill-defined and unspecified causes of mortality',
            'th' => 'การตายที่ไม่ทราบสาเหตุหรือไม่ได้ระบุรายละเอียด'
        ],
        'J189' => [
            'en' => 'Pneumonia, unspecified',
            'th' => 'ปอดอักเสบ ไม่ระบุรายละเอียด'
        ],
        'A419' => [
            'en' => 'Septicaemia, unspecified',
            'th' => 'ภาวะโลหิตเป็นพิษ ไม่ระบุรายละเอียด'
        ],
        'G311' => [
            'en' => 'Senile degeneration of brain, not elsewhere classified',
            'th' => 'สมองเสื่อมในวัยชรา มิได้จำแนกไว้ที่ใด'
        ],
        'G319' => [
            'en' => 'Degenerative disease of nervous system, unspecified',
            'th' => 'โรคระบบประสาทเสื่อม ไม่ระบุรายละเอียด'
        ],
        'C229' => [
            'en' => 'Malignant neoplasm of liver, primary',
            'th' => 'มะเร็งตับ ชนิดปฐมภูมิ'
        ],
        'E149' => [
            'en' => 'Unspecified diabetes mellitus',
            'th' => 'โรคเบาหวาน ไม่ระบุรายละเอียด'
        ],
        'R54' => [
            'en' => 'Senility',
            'th' => 'ความชรา'
        ],
        'I619' => [
            'en' => 'Intracerebral haemorrhage, unspecified',
            'th' => 'เลือดออกในสมอง ไม่ระบุรายละเอียด'
        ],
        'C349' => [
            'en' => 'Malignant neoplasm of bronchus or lung, unspecified',
            'th' => 'มะเร็งหลอดลมหรือปอด ไม่ระบุรายละเอียด'
        ],
        'N19' => [
            'en' => 'Unspecified kidney failure',
            'th' => 'ไตวาย ไม่ระบุรายละเอียด'
        ],
        'N185' => [
            'en' => 'Chronic kidney disease, stage 5',
            'th' => 'โรคไตเรื้อรัง ระยะที่ 5'
        ],
        'I64' => [
            'en' => 'Stroke, not specified as haemorrhage or infarction',
            'th' => 'โรคหลอดเลือดสมอง ไม่ระบุว่าเป็นแบบตกเลือดหรืออุดตัน'
        ],
        'N189' => [
            'en' => 'Chronic kidney disease, unspecified',
            'th' => 'โรคไตเรื้อรัง ไม่ระบุรายละเอียด'
        ],
        'C240' => [
            'en' => 'Malignant neoplasm of extrahepatic bile duct',
            'th' => 'มะเร็งท่อน้ำดีนอกตับ'
        ],
        'V892' => [
            'en' => 'Person injured in unspecified motor-vehicle accident',
            'th' => 'ผู้บาดเจ็บจากอุบัติเหตุยานยนต์ที่ไม่ระบุรายละเอียด'
        ],
        'K746' => [
            'en' => 'Other and unspecified cirrhosis of liver',
            'th' => 'ตับแข็งแบบอื่นและที่ไม่ระบุรายละเอียด'
        ],
        'Y349' => [
            'en' => 'Unspecified event, undetermined intent',
            'th' => 'เหตุการณ์ที่ไม่ระบุรายละเอียดและไม่ทราบเจตนา'
        ],
        'N179' => [
            'en' => 'Acute kidney failure, unspecified',
            'th' => 'ไตวายเฉียบพลัน ไม่ระบุรายละเอียด'
        ],
        'I609' => [
            'en' => 'Subarachnoid haemorrhage, unspecified',
            'th' => 'เลือดออกใต้เยื่อหุ้มสมองชั้นกลาง ไม่ระบุรายละเอียด'
        ],
        'X709' => [
            'en' => 'Intentional self-harm by hanging, strangulation and suffocation',
            'th' => 'การจงใจทำร้ายตนเองด้วยการแขวนคอ รัดคอ และทำให้ขาดอากาศหายใจ'
        ],
        'C260' => [
            'en' => 'Malignant neoplasm of intestinal tract, part unspecified',
            'th' => 'มะเร็งทางเดินอาหาร ส่วนที่ไม่ระบุรายละเอียด'
        ],
        'C800' => [
            'en' => 'Malignant neoplasm, primary site unknown, so described',
            'th' => 'มะเร็งไม่ระบุตำแหน่งปฐมภูมิ'
        ],
        'I10' => [
            'en' => 'Essential (primary) hypertension',
            'th' => 'โรคความดันโลหิตสูงชนิดปฐมภูมิ'
        ],
        'E872' => [
            'en' => 'Acidosis',
            'th' => 'ภาวะกรดเกินในร่างกาย'
        ],
        'I249' => [
            'en' => 'Acute ischemic heart disease, unspecified',
            'th' => 'โรคหัวใจขาดเลือดเฉียบพลัน ไม่ระบุรายละเอียด'
        ],
        'W749' => [
            'en' => 'Unspecified drowning and submersion',
            'th' => 'การจมน้ำและจมอยู่ใต้น้ำ ไม่ระบุรายละเอียด'
        ],
        'I255' => [
            'en' => 'Ischemic cardiomyopathy',
            'th' => 'โรคกล้ามเนื้อหัวใจขาดเลือด'
        ],
        'C509' => [
            'en' => 'Malignant neoplasm of breast, unspecified',
            'th' => 'มะเร็งเต้านม ไม่ระบุรายละเอียด'
        ],
        'K922' => [
            'en' => 'Gastrointestinal haemorrhage, unspecified',
            'th' => 'เลือดออกในทางเดินอาหาร ไม่ระบุรายละเอียด'
        ],
    ];

    /**
     * Names of the 21 MOPH cause groups (รง.504).
     */
    protected static $groups = [
        1 => 'โรคติดเชื้อและพยาธิ (A00-B99)',
        2 => 'เนื้องอกและมะเร็ง (C00-D48)',
        3 => 'โรคเลือดและอวัยวะสร้างเลือด (D50-D89)',
        4 => 'โรคต่อมไร้ท่อ โภชนาการ และเมตาบอลิซึม (E00-E90)',
        5 => 'ภาวะแปรปรวนทางจิตและพฤติกรรม (F00-F99)',
        6 => 'โรคระบบประสาท (G00-G99)',
        7 => 'โรคตาและส่วนประกอบของตา (H00-H59)',
        8 => 'โรคหูและปุ่มกกหู (H60-H95)',
        9 => 'โรคระบบไหลเวียนเลือด (I00-I99)',
        10 => 'โรคระบบหายใจ (J00-J99)',
        11 => 'โรคระบบย่อยอาหาร (K00-K93)',
        12 => 'โรคผิวหนังและเยื่อใต้ผิวหนัง (L00-L99)',
        13 => 'โรคระบบกล้ามเนื้อและโครงร่าง (M00-M99)',
        14 => 'โรคระบบปัสสาวะและสืบพันธุ์ (N00-N99)',
        15 => 'ภาวะแทรกซ้อนการตั้งครรภ์ คลอด และหลังคลอด (O00-O99)',
        16 => 'ภาวะผิดปกติในทารกแรกเกิด (P00-P96)',
        17 => 'ความพิการแต่กำเนิดและโครโมโซมผิดปกติ (Q00-Q99)',
        18 => 'อาการ/สิ่งผิดปกติที่ไม่ระบุรายละเอียด (R00-R99)',
        19 => 'การเป็นพิษ ผลจากสาเหตุภายนอก (S00-T98, X40-X49, X60-X69, X85-Y19)',
        20 => 'อุบัติเหตุจากการขนส่ง (V01-V99, Y85)',
        21 => 'สาเหตุภายนอกอื่นๆ ที่ทำให้บาดเจ็บหรือตาย (W00-Y89)',
    ];

    /**
     * Get description for an ICD-10 code.
     */
    public static function getDescription(?string $code): string
    {
        $code = trim($code ?? '');
        if (empty($code)) {
            return 'ไม่บันทึกรหัสโรค';
        }

        $codeUpper = strtoupper($code);
        if (isset(self::$descriptions[$codeUpper])) {
            return '[' . $codeUpper . '] ' . self::$descriptions[$codeUpper]['th'] . ' (' . self::$descriptions[$codeUpper]['en'] . ')';
        }

        return '[' . $codeUpper . '] (ไม่ระบุรายละเอียดโรค)';
    }

    /**
     * Map an ICD-10 code to one of the 21 groups.
     */
    public static function getGroupNumber(?string $code): int
    {
        $code = trim(strtoupper($code ?? ''));
        if (empty($code)) {
            return 18; // Default to NEC/Symptoms
        }

        $letter = $code[0];
        $numberPart = (int)preg_replace('/[^0-9]/', '', substr($code, 1, 2));

        switch ($letter) {
            case 'A':
            case 'B':
                return 1;
            case 'C':
                return 2;
            case 'D':
                return ($numberPart <= 48) ? 2 : 3;
            case 'E':
                return 4;
            case 'F':
                return 5;
            case 'G':
                return 6;
            case 'H':
                return ($numberPart <= 59) ? 7 : 8;
            case 'I':
                return 9;
            case 'J':
                return 10;
            case 'K':
                return 11;
            case 'L':
                return 12;
            case 'M':
                return 13;
            case 'N':
                return 14;
            case 'O':
                return 15;
            case 'P':
                return 16;
            case 'Q':
                return 17;
            case 'R':
                return 18;
            case 'S':
            case 'T':
                return 19;
            case 'V':
                return 20;
            case 'W':
                return 21;
            case 'X':
                if ($numberPart >= 40 && $numberPart <= 49) return 19;
                if ($numberPart >= 60 && $numberPart <= 69) return 19;
                return 21;
            case 'Y':
                if (str_starts_with($code, 'Y85')) return 20;
                if ($numberPart <= 19) return 19;
                return 21;
            default:
                return 18;
        }
    }

    /**
     * Get the name of a group by number.
     */
    public static function getGroupName(int $groupNum): string
    {
        return self::$groups[$groupNum] ?? 'กลุ่มอื่นๆ';
    }

    /**
     * Get all cause group definitions.
     */
    public static function getAllGroups(): array
    {
        return self::$groups;
    }
}
