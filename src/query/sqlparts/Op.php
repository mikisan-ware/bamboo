<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ORM
 * Start Date  : 2021/09/10
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\bamboo;

use \mikisan\core\exception\BambooException;

class Op
{

    const   MATCH = "MATCH", NOTMATCH = "<>", NOT = "NOT", 
            EQ = "=", NOTEQ= "!=", 
            LT = "<", GT = ">", LTE = "<=", GTE = ">=",
            IN = "IN", NOTIN = "NOTIN",
            ISNULL = "--ISNULL", ISNOTNULL = "--ISNOTNULL", 
            BETWEEN = "BETWEEN", NOTBETWEEN = "NOTBETWEEN", 
            
            LIKE = "LIKE", 
            LIKEW = "LIKE%", WLIKE = "%LIKE", LIKE_ = "LIKE_", _LIKE = "_LIKE", 
            NOTLIKE = "NOTLIKE", 
            NOTLIKEW = "NOTLIKE%", NOTWLIKE = "NOT%LIKE", 
            NOTLIKE_ = "NOTLIKE_", NOT_LIKE = "NOT_LIKE", 
            
            EXISTS = "EXISTS", NOTEXISTS = "NOTEXISTS";
    
}
