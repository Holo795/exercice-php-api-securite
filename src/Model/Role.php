<?php

namespace App\Model;

// Enum representing various user roles in the application
enum Role: string
{
    // Defined role values
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';
    case MANAGER = 'ROLE_MANAGER';
    case CONSULTANT = 'ROLE_CONSULTANT';
    case NONE = 'ROLE_NONE';

    // Method to return all the roles as an array
    public static function getRolesArray(): array
    {
        return [
            self::USER->value,
            self::ADMIN->value,
            self::MANAGER->value,
            self::CONSULTANT->value,
        ];
    }

    // Method to return an integer value for each role
    public function getRoleValue(): int
    {
        return match ($this) {
            self::USER => 0,
            self::CONSULTANT => 1,
            self::MANAGER => 2,
            self::ADMIN => 3,
            self::NONE => -1,
        };
    }

    // Method to convert a string representation of a role to the corresponding enum value
    public static function roleFromStrValue(string $role): Role
    {
        return match ($role) {
            self::USER->value => self::USER,
            self::CONSULTANT->value => self::CONSULTANT,
            self::MANAGER->value => self::MANAGER,
            self::ADMIN->value => self::ADMIN,
        };
    }
}
