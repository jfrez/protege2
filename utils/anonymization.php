<?php
if (!function_exists('hash_identifier')) {
    function hash_identifier($value, int $length = 10): string
    {
        if (empty($value)) {
            return 'ANONIMIZADO';
        }
        return 'ANON-' . strtoupper(substr(hash('sha256', (string) $value), 0, $length));
    }
}

if (!function_exists('anonymize_sensitive_fields')) {
    function anonymize_sensitive_fields(array $row): array
    {
        $hashFragments = ['rut', 'cod_nino', 'identificador', 'telefono', 'celular', 'dni', 'pasaporte'];
        $maskFragments = ['nombre', 'apellido', 'evaluador', 'direccion', 'correo', 'email', 'apoderado', 'tutor', 'madre', 'padre', 'contacto', 'nacimiento'];

        foreach ($row as $key => $value) {
            if ($value instanceof DateTime) {
                continue;
            }

            $lowerKey = strtolower((string) $key);

            foreach ($hashFragments as $fragment) {
                if (strpos($lowerKey, $fragment) !== false) {
                    $row[$key] = hash_identifier($value);
                    continue 2;
                }
            }

            foreach ($maskFragments as $fragment) {
                if (strpos($lowerKey, $fragment) !== false) {
                    $row[$key] = 'ANONIMIZADO';
                    continue 2;
                }
            }
        }

        return $row;
    }
}

if (!function_exists('build_supervisor_display')) {
    function build_supervisor_display(array $row): array
    {
        $row['display_name'] = 'Caso #' . str_pad((string) ($row['id'] ?? '0'), 4, '0', STR_PAD_LEFT);
        $row['display_rut'] = hash_identifier($row['rut'] ?? '');
        $row['display_cod_nino'] = hash_identifier($row['cod_nino'] ?? '');
        $row['display_evaluador'] = 'Equipo Evaluador';
        $row['can_view_details'] = false;

        return $row;
    }
}

if (!function_exists('build_standard_display')) {
    function build_standard_display(array $row): array
    {
        $row['display_name'] = $row['nombre'] ?? 'Sin nombre';
        $row['display_rut'] = $row['rut'] ?? 'Sin RUT';
        $row['display_cod_nino'] = $row['cod_nino'] ?? '';
        $row['display_evaluador'] = $row['evaluador_nombre'] ?? ($row['evaluador'] ?? '');
        $row['can_view_details'] = true;

        return $row;
    }
}
