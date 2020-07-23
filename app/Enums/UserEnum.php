<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class UserEnum extends Enum {

    private const ADMIN_ABILITIES = [
        //user
        'user:create', 
        'user:mine:list',
        'user:mine:view', 
        'user:mine:edit', 
        'user:mine:delete',
        'user:others:list',
        'user:others:view', 
        'user:others:edit', 
        'user:others:delete',
        //group
        'group:create',
        'group:mine:list',
        'group:mine:view',
        'group:mine:edit',
        'group:mine:delete',
        'group:others:list',
        'group:others:view',
        'group:others:edit',
        'group:others:delete',
        //task
        'task:create',
        'task:mine:list',
        'task:mine:view',
        'task:mine:edit',
        'task:mine:delete',
        'task:others:list',
        'task:others:view',
        'task:others:edit',
        'task:others:delete'
    ];

    private const TEACHER_ABILITIES = [
        //user
        'user:create', 
        'user:mine:list',
        'user:mine:view', 
        'user:mine:edit', 
        'user:mine:delete',
        //group
        'group:create',
        'group:mine:list',
        'group:mine:view',
        'group:mine:edit',
        'group:mine:delete',
        //task
        'task:create',
        'task:mine:list',
        'task:mine:view',
        'task:mine:edit',
        'task:mine:delete'
    ];

    private const STUDENT_ABILITIES = [
        //user
        'user:mine:view',
        //group
        'group:mine:list',
        'group:mine:view',
        //task
        'task:mine:list',
        'task:mine:view',
    ];

    private CONST ADMIN = 1;

    private CONST TEACHER = 2;

    private CONST STUDENT = 3;

    public static function ADMIN_ABILITIES() {
        return self::ADMIN_ABILITIES;
    }

    public static function TEACHER_ABILITIES() {
        return self::TEACHER_ABILITIES;
    }

    public static function STUDENT_ABILITIES() {
        return self::STUDENT_ABILITIES;
    }

    public static function ADMIN() {
        return self::ADMIN;
    }

    public static function TEACHER() {
        return self::TEACHER;
    }

    public static function STUDENT() {
        return self::STUDENT;
    }

}