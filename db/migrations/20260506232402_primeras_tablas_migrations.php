<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PrimerasTablasMigrations extends AbstractMigration
{
    public function change(): void
    {
        // Tabla de usuarios (regular, entrenador, gimnasio)
        $usuarios = $this->table('usuarios');
        $usuarios
            ->addColumn('nombre_apellido', 'string', ['limit' => 150])
            ->addColumn('email', 'string', ['limit' => 150])
            ->addColumn('telefono', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('tipo_cuenta', 'enum', ['values' => ['regular', 'entrenador', 'gimnasio'], 'default' => 'regular'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['email'], ['unique' => true])
            ->create();

        // Tabla de gimnasios
        $gimnasios = $this->table('gimnasios');
        $gimnasios
            ->addColumn('nombre', 'string', ['limit' => 150])
            ->addColumn('email', 'string', ['limit' => 150])
            ->addColumn('telefono', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('direccion', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('descripcion', 'text', ['null' => true])
            ->addColumn('horario', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('servicios', 'text', ['null' => true])
            ->addColumn('logo', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['email'], ['unique' => true])
            ->create();

        // Tabla de entrenadores
        $entrenadores = $this->table('entrenadores');
        $entrenadores
            ->addColumn('nombre', 'string', ['limit' => 150])
            ->addColumn('email', 'string', ['limit' => 150])
            ->addColumn('telefono', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('descripcion', 'text', ['null' => true])
            ->addColumn('especialidad', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('horario', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('gimnasio_id', 'integer', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['email'], ['unique' => true])
            ->addForeignKey('gimnasio_id', 'gimnasios', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        // Tabla de rutinas
        $rutinas = $this->table('rutinas');
        $rutinas
            ->addColumn('titulo', 'string', ['limit' => 150])
            ->addColumn('descripcion', 'text', ['null' => true])
            ->addColumn('objetivo', 'string', ['limit' => 150, 'null' => true])
            ->addColumn('fecha_inicio', 'date', ['null' => true])
            ->addColumn('fecha_fin', 'date', ['null' => true])
            ->addColumn('entrenador_id', 'integer', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('entrenador_id', 'entrenadores', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        // Tabla de ejercicios
        $ejercicios = $this->table('ejercicios');
        $ejercicios
            ->addColumn('nombre', 'string', ['limit' => 150])
            ->addColumn('descripcion', 'text', ['null' => true])
            ->addColumn('instrucciones', 'text', ['null' => true])
            ->addColumn('dificultad', 'enum', ['values' => ['baja', 'media', 'alta'], 'default' => 'media'])
            ->addColumn('musculos', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('equipamiento', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('video_url', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        // Tabla de entrenamientos (días dentro de una rutina)
        $entrenamientos = $this->table('entrenamientos');
        $entrenamientos
            ->addColumn('rutina_id', 'integer')
            ->addColumn('nombre_dia', 'string', ['limit' => 100])
            ->addColumn('grupo_muscular', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('duracion_minutos', 'integer', ['null' => true])
            ->addColumn('orden', 'integer', ['default' => 1])
            ->addForeignKey('rutina_id', 'rutinas', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();

        // Tabla pivote entrenamiento-ejercicios
        $entrenamientoEjercicios = $this->table('entrenamiento_ejercicios');
        $entrenamientoEjercicios
            ->addColumn('entrenamiento_id', 'integer')
            ->addColumn('ejercicio_id', 'integer')
            ->addColumn('series_repeticiones', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('orden', 'integer', ['default' => 1])
            ->addForeignKey('entrenamiento_id', 'entrenamientos', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('ejercicio_id', 'ejercicios', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();

        // Tabla de suscripciones
        $suscripciones = $this->table('suscripciones');
        $suscripciones
            ->addColumn('usuario_id', 'integer')
            ->addColumn('plan', 'string', ['limit' => 100])
            ->addColumn('precio', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('estado', 'enum', ['values' => ['activa', 'cancelada', 'vencida'], 'default' => 'activa'])
            ->addColumn('fecha_inicio', 'date')
            ->addColumn('fecha_fin', 'date', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('usuario_id', 'usuarios', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
