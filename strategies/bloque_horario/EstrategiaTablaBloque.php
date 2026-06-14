<?php
require_once __DIR__ . '/EstrategiaFormatoBloque.php';

class EstrategiaTablaBloque implements EstrategiaFormatoBloque
{
	public function execute(array $data): string
	{
		$html = '<table>';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>ID</th>';
		$html .= '<th>Hora Inicio</th>';
		$html .= '<th>Hora Fin</th>';
		$html .= '<th>Cupos</th>';
		$html .= '<th>Periodo</th>';
		$html .= '<th>Acciones</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		if (count($data) === 0) {
			$html .= '<tr><td colspan="6">No hay bloques de horario registrados.</td></tr>';
		} else {
			foreach ($data as $bloque) {
				$id = (int)($bloque['id'] ?? 0);
				$ini = (string)($bloque['hora_inicio'] ?? '');
				$fin = (string)($bloque['hora_fin'] ?? '');
				$cupos = (int)($bloque['cantidad_cupos'] ?? 0);
				$idPeriodo = (int)($bloque['idperiodo'] ?? 0);
				$semestre = (string)($bloque['semestre'] ?? '');

				$html .= '<tr>';
				$html .= '<td>' . $id . '</td>';
				$html .= '<td>' . $this->escapar($ini) . '</td>';
				$html .= '<td>' . $this->escapar($fin) . '</td>';
				$html .= '<td>' . $cupos . '</td>';
				$html .= '<td>' . $this->escapar($semestre) . '</td>';
				$html .= '<td>';
				$html .= $this->construirAcciones($id, $ini, $fin, $cupos, $idPeriodo);
				$html .= '</td>';
				$html .= '</tr>';
			}
		}

		$html .= '</tbody>';
		$html .= '</table>';

		return $html;
	}

	private function construirAcciones(int $id, string $ini, string $fin, int $cupos, int $idPeriodo): string
	{
		$html = '<div class="acciones">';
		$html .= '<form method="post" action="index.php?accion_usuario=gestion_bloquehorario" style="margin:0;">';
		$html .= '<input type="hidden" name="accion_bloque" value="editar" />';
		$html .= '<input type="hidden" name="accion_estrategia" value="ver_tabla" />';
		$html .= '<input type="hidden" name="id_bloque" value="' . $id . '" />';
		$html .= '<input type="hidden" name="hora_inicio" value="' . $this->escapar($ini) . '" />';
		$html .= '<input type="hidden" name="hora_fin" value="' . $this->escapar($fin) . '" />';
		$html .= '<input type="hidden" name="cantidad_cupos" value="' . $cupos . '" />';
		$html .= '<input type="hidden" name="id_periodo" value="' . $idPeriodo . '" />';
		$html .= '<button class="btn btn-light" type="submit">Editar</button>';
		$html .= '</form>';
		$html .= '<form method="post" action="index.php?accion_usuario=gestion_bloquehorario" style="margin:0;" onsubmit="return confirm(\'Deseas eliminar este bloque de horario?\');">';
		$html .= '<input type="hidden" name="accion_bloque" value="eliminar" />';
		$html .= '<input type="hidden" name="accion_estrategia" value="ver_tabla" />';
		$html .= '<input type="hidden" name="id_bloque" value="' . $id . '" />';
		$html .= '<button class="btn btn-danger" type="submit">Eliminar</button>';
		$html .= '</form>';
		$html .= '</div>';

		return $html;
	}

	private function escapar(string $valor): string
	{
		return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
	}
}
