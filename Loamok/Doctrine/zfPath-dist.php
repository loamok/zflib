<?php
set_include_path(implode(PATH_SEPARATOR, array(
    realpath("Youre_Zf_Fullpath"), // /usr/share/php/libzend-framework-php
    get_include_path(),
)));