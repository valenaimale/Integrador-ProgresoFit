<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PrimerasTablasMigrations extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $tableGimnasio = $this->table('gimnasio');
        $tableGimnasio->addColumn('nombre', 'string', '['limit'=>60]');//addColumn recibe como primer parametro 
        //el nombre de la columna, el tipo de datos como segundo parametro y un tercer parametro opcional de una posible opcion
        //si no se especifica nada, la columna es not null
        $tableGimnasio->addColumn('mail','string', '['limit'=>40]');
        $tableGimnasio->addColumn('contrasenia','string', '['limit'=>40]');
        $tableGimnasio->addColumn('direccion','string', '['limit'=>40]');
        $tableGimnasio->addColumn('mail','string', '['limit'=>40]');





    }
}
