<?php

class Validation
{
    public static function name($name)
    {
        // Noņem liekas atstarpes
        $name = trim($name);

        // Nedrīkst būt tukšs
        if ($name === "") {
            return false;
        }

        // Tikai burti + atstarpes + domuzīmes
        if (!preg_match("/^[A-Za-zĀ-ž\s\-]+$/u", $name)) {
            return false;
        }

        // Minimālais garums
        if (strlen($name) < 2) {
            return false;
        }

        return true;
    }
}
