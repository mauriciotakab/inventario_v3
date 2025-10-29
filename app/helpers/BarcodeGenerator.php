<?php

class BarcodeGenerator
{
    /**
     * Generates the bar/space sequence for Code39.
     * Each element is an array [type, widthUnits] where type is 'bar' or 'space'.
     */
    public static function code39Pattern(string $text): array
    {
        $map = [
            '0' => 'nnnwwnwnw',
            '1' => 'wnnwnnnnw',
            '2' => 'nnwwnnnnw',
            '3' => 'wnwwnnnnn',
            '4' => 'nnnwwnnnw',
            '5' => 'wnnwwnnnn',
            '6' => 'nnwwwnnnn',
            '7' => 'nnnwnnwnw',
            '8' => 'wnnwnnwnn',
            '9' => 'nnwwnnwnn',
            'A' => 'wnnnnwnnw',
            'B' => 'nnwnnwnnw',
            'C' => 'wnwnnwnnn',
            'D' => 'nnnnwwnnw',
            'E' => 'wnnnwwnnn',
            'F' => 'nnwnwwnnn',
            'G' => 'nnnnnwwnw',
            'H' => 'wnnnnwwnn',
            'I' => 'nnwnnwwnn',
            'J' => 'nnnnwwwnn',
            'K' => 'wnnnnnnww',
            'L' => 'nnwnnnnww',
            'M' => 'wnwnnnnwn',
            'N' => 'nnnnwnnww',
            'O' => 'wnnnwnnwn',
            'P' => 'nnwnwnnwn',
            'Q' => 'nnnnnnwww',
            'R' => 'wnnnnnwwn',
            'S' => 'nnwnnnwwn',
            'T' => 'nnnnwnwwn',
            'U' => 'wwnnnnnnw',
            'V' => 'nwwnnnnnw',
            'W' => 'wwwnnnnnn',
            'X' => 'nwnnwnnnw',
            'Y' => 'wwnnwnnnn',
            'Z' => 'nwwnwnnnn',
            '-' => 'nwnnnnwnw',
            '.' => 'wwnnnnwnn',
            ' ' => 'nwwnnnwnn',
            '$' => 'nwnwnwnnn',
            '/' => 'nwnwnnnwn',
            '+' => 'nwnnnwnwn',
            '%' => 'nnnwnwnwn',
            '*' => 'nwnnwnwnn',
        ];

        $text = strtoupper($text);
        $sequence = [];
        $chars = str_split('*' . $text . '*');

        foreach ($chars as $index => $char) {
            if (!isset($map[$char])) {
                throw new InvalidArgumentException("Caracter no soportado en codigo de barras: {$char}");
            }
            $pattern = $map[$char];
            for ($i = 0; $i < strlen($pattern); $i++) {
                $type = ($i % 2 === 0) ? 'bar' : 'space';
                $width = $pattern[$i] === 'n' ? 1 : 3;
                $sequence[] = [$type, $width];
            }
            if ($index !== count($chars) - 1) {
                $sequence[] = ['space', 1];
            }
        }

        return $sequence;
    }
}
