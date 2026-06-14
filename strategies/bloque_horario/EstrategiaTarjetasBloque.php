<?php
require_once __DIR__ . '/EstrategiaFormatoBloque.php';

class EstrategiaTarjetasBloque implements EstrategiaFormatoBloque
{
	public function execute(array $data): string
	{
		if (count($data) === 0) {
			return '<div class="listado-vacio">No hay bloques de horario registrados.</div>';
		}

		$html = '<div class="grid-horarios">';

		foreach ($data as $bloque) {
			$id = (int)($bloque['id'] ?? 0);
			$ini = (string)($bloque['hora_inicio'] ?? '');
			$fin = (string)($bloque['hora_fin'] ?? '');
			$cupos = (int)($bloque['cantidad_cupos'] ?? 0);
			$idPeriodo = (int)($bloque['idperiodo'] ?? 0);
			$semestre = (string)($bloque['semestre'] ?? '');

			$html .= '<div class="card-horario">';
			$html .= '<div class="card-horario-id">Bloque #' . $id . '</div>';
			$html .= '<div class="card-horario-horas">' . $this->escapar($ini) . ' - ' . $this->escapar($fin) . '</div>';
			$html .= '<div class="card-horario-meta">';
			$html .= '<span>Cupos: ' . $cupos . '</span>';
			$html .= '<span>Periodo: ' . $this->escapar($semestre) . '</span>';
			$html .= '</div>';
			$html .= $this->construirAcciones($id, $ini, $fin, $cupos, $idPeriodo);
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	private function construirAcciones(int $id, string $ini, string $fin, int $cupos, int $idPeriodo): string
	{
		$html = '<div class="acciones card-horario-acciones">';
		$html .= '<form method="post" action="index.php?accion_usuario=gestion_bloquehorario" style="margin:0;">';
		$html .= '<input type="hidden" name="accion_bloque" value="editar" />';
		$html .= '<input type="hidden" name="accion_estrategia" value="ver_tarjetas" />';
		$html .= '<input type="hidden" name="id_bloque" value="' . $id . '" />';
		$html .= '<input type="hidden" name="hora_inicio" value="' . $this->escapar($ini) . '" />';
		$html .= '<input type="hidden" name="hora_fin" value="' . $this->escapar($fin) . '" />';
		$html .= '<input type="hidden" name="cantidad_cupos" value="' . $cupos . '" />';
		$html .= '<input type="hidden" name="id_periodo" value="' . $idPeriodo . '" />';
		$html .= '<button class="btn btn-light" type="submit">Editar</button>';
		$html .= '</form>';
		$html .= '<form method="post" action="index.php?accion_usuario=gestion_bloquehorario" style="margin:0;" onsubmit="return confirm(\'Deseas eliminar este bloque de horario?\');">';
		$html .= '<input type="hidden" name="accion_bloque" value="eliminar" />';
		$html .= '<input type="hidden" name="accion_estrategia" value="ver_tarjetas" />';
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
