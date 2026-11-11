<?php
declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use PDO;

/** Punto único de invocación de Stored Procedures mediante PDO. */
final class Procedimientos
{
    public static function filas(PDO $db, string $nombre, array $parametros = []): array
    {
        // El nombre del procedimiento no es un parámetro SQL: restringir su forma.
        if (!preg_match('/\Asp_[a-z_]+\z/D', $nombre)) {
            throw new InvalidArgumentException('Procedimiento no permitido.');
        }

        $marcas = implode(',', array_fill(0, count($parametros), '?'));
        $consulta = $db->prepare('CALL ' . $nombre . '(' . $marcas . ')');
        foreach (array_values($parametros) as $posicion => $valor) {
            $tipo = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $consulta->bindValue($posicion + 1, $valor, $tipo);
        }

        $consulta->execute();
        try {
            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } finally {
            // Cierra resultados del procedimiento antes de emitir otro CALL en la conexión.
            $consulta->closeCursor();
        }
    }

    public static function uno(PDO $db, string $nombre, array $parametros = []): ?array
    {
        return self::filas($db, $nombre, $parametros)[0] ?? null;
    }
}
