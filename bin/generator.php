<?php

        $classNames = array(
            'Answer',
            'Category',
            'Geoname',
            'Question',
            'Questionnaire',
            'Role',
            'Setting',
            'Survey',
            'User',
        );
foreach ($classNames as $className)
{

$model = <<< STRING
<?php

namespace Application\Model;

class $className extends AbstractModel
{
    
}

STRING;

$mapper = <<< STRING
<?php

namespace Application\Mapper;

class {$className}Mapper extends AbstractMapper
{

}

STRING;

	echo "Writing files ...\n";
	$dir = dirname(__FILE__) . '/../module/Application/src/Application/';
	file_put_contents("$dir/Model/$className.php", $model);
	file_put_contents("$dir/Mapper/{$className}Mapper.php", $mapper);
	echo "Writing files done :-)\n";
}

echo "_____________Model________________________________________________________________________________________________________\n";
echo $model;
echo "_____________Mapper_______________________________________________________________________________________________________\n";
echo $mapper;
echo "__________________________________________________________________________________________________________________________\n";
echo "\n";
