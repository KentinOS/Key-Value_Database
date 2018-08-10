<?php
/**
 * The Global Config
 */
#DataBase's basic function:open/close+CURD
    
    #Fundational Config
    define('DB_CONFIG_PREFIX',     'DB_');

    #Common-Return-value
    define(DB_CONFIG_PREFIX.'SUCCESS',        0);
    define(DB_CONFIG_PREFIX.'FAILED',         -1);
    define(DB_CONFIG_PREFIX.'KEY_EXISTS',     1);
    define(DB_CONFIG_PREFIX.'KEY_NOT_EXISTS', 2);
    define(DB_CONFIG_PREFIX.'KEY_INVALID',    -2);

    #file-mode
    define(DB_CONFIG_PREFIX.'PUBLIC_MODE','w+b');
    define(DB_CONFIG_PREFIX.'PROTECT_MODE','r+b');
    define(DB_CONFIG_PREFIX.'PRIVATE_MODE','w+r');

    #Index-file
    define(DB_CONFIG_PREFIX.'INDEX_SUFFIX',   '.idx');
    define(DB_CONFIG_PREFIX.'BUCKET_SIZE',    262144);
    define(DB_CONFIG_PREFIX.'KEY_SIZE',       128);
    define(DB_CONFIG_PREFIX.'INDEX_SIZE',     128+12);

    #data-file
    define(DB_CONFIG_PREFIX.'DATA_SUFFIX',    '.dat');

    #-----------------------------------------------------------------------------------------------------
    #Function

    #Open
    #TODO

    #close
    #TODO

    #create
    #TODO

    #Update    
    define(DB_CONFIG_PREFIX."INSERT",     1);
    define(DB_CONFIG_PREFIX."REPLACE",    2);
    define(DB_CONFIG_PREFIX."STORE",      DB_CONFIG_PREFIX.'INSERT'|DB_CONFIG_PREFIX.'REPLACE');
    
    #Read
    #TODO

    #Delete
    #TODO

    #-----------------------------------------------------------------------------------------------------
