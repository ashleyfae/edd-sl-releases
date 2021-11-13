<?php
namespace PHPSTORM_META {
    // Allow PhpStorm IDE to resolve return types when calling eddSlReleases(Object_Type::class) or eddSlReleases(`Object_Type`).
    override(
        \eddSlReleases( 0 ),
        map( [
            '' => '@',
            '' => '@Class',
        ] )
    );
}
