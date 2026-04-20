<?php

class bloqueHorario_view
{
	public bool $modoEdicion;
	public int $idSel;
	public string $iniSel;
	public string $finSel;
	public int $cuposSel;
	public int $idPerSel;
	public array $lista;
	public array $listaPeriodos;
	public string $tituloAccion;
	public string $colorAlerta;

	public function __construct()
	{
		$this->limpiar();
	}

	public function sincronizar(
		bool $modoEdicion,
		int $idSel,
		string $iniSel,
		string $finSel,
		int $cuposSel,
		int $idPerSel,
		array $lista,
		array $listaPeriodos
	): void {
		$this->modoEdicion = $modoEdicion;
		$this->idSel = $idSel;
		$this->iniSel = $iniSel;
		$this->finSel = $finSel;
		$this->cuposSel = $cuposSel;
		$this->idPerSel = $idPerSel;
		$this->lista = $lista;
		$this->listaPeriodos = $listaPeriodos;
	}

	public function listar(array $lista): void
	{
		$this->lista = $lista;
		$this->tituloAccion = $this->modoEdicion ? 'Modificar Bloque de Horario' : 'Registrar Bloque de Horario';
		$this->colorAlerta = $this->modoEdicion ? '#fef9ea' : '#eaf2fe';
		$this->render('');
	}

	public function insertar(string $msg): void
	{
		$this->tituloAccion = 'Registrar Bloque de Horario';
		$this->colorAlerta = '#eaf2fe';
		$this->render($msg);
	}

	public function actualizar(string $msg): void
	{
		$this->tituloAccion = 'Modificar Bloque de Horario';
		$this->colorAlerta = '#fef9ea';
		$this->render($msg);
	}

	public function eliminar(string $msg): void
	{
		$this->tituloAccion = 'Eliminar Bloque de Horario';
		$this->colorAlerta = '#fdecec';
		$this->render($msg);
	}

	public function limpiar(): void
	{
		$this->modoEdicion = false;
		$this->idSel = 0;
		$this->iniSel = '';
		$this->finSel = '';
		$this->cuposSel = 1;
		$this->idPerSel = 0;
		$this->lista = array();
		$this->listaPeriodos = array();
		$this->tituloAccion = 'Registrar Bloque de Horario';
		$this->colorAlerta = '#eaf2fe';
	}

	private function render(string $msg): void
	{
		$accionGuardar = $this->modoEdicion ? 'actualizar' : 'insertar';
		?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>DocuFlow - Gestionar Bloque de Horario</title>
	<style>
		:root {
			--bg: #f4f6f9;
			--panel: #ffffff;
			--line: #d7dfe7;
			--text: #1f2937;
			--muted: #5d6d7e;
			--primary: #0f5cc0;
			--danger: #ab1f1f;
			--ok: #226f3a;
		}
		* { box-sizing: border-box; }
		body {
			margin: 0;
			font-family: "Segoe UI", Tahoma, Arial, sans-serif;
			background: var(--bg);
			color: var(--text);
		}
		.contenedor {
			max-width: 980px;
			margin: 24px auto;
			padding: 0 16px;
		}
		.panel {
			background: var(--panel);
			border: 1px solid var(--line);
			border-radius: 12px;
			padding: 18px;
			box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
		}
		h1 {
			margin: 0 0 14px;
			font-size: 24px;
		}
		.sub {
			margin: 0 0 16px;
			color: var(--muted);
		}
		.mensaje {
			margin-bottom: 14px;
			padding: 10px 12px;
			border-radius: 10px;
			background: #eaf2fe;
			border: 1px solid #c8daf7;
		}
		.form-grid {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 10px;
		}
		.form-fila {
			display: grid;
			grid-template-columns: 1fr auto;
			gap: 10px;
			margin-top: 10px;
		}
		@media (max-width: 700px) {
			.form-grid {
				grid-template-columns: 1fr;
			}
			.form-fila {
				grid-template-columns: 1fr;
			}
		}
		label {
			display: block;
			margin-bottom: 6px;
			font-weight: 600;
		}
		input,
		select {
			width: 100%;
			padding: 10px 12px;
			border: 1px solid #c6d1dc;
			border-radius: 10px;
			font-size: 15px;
			background: #fff;
		}
		.btn {
			border: none;
			border-radius: 10px;
			padding: 10px 14px;
			font-weight: 600;
			cursor: pointer;
		}
		.btn-primary { background: var(--primary); color: #fff; }
		.btn-danger { background: #fdecec; color: var(--danger); }
		.btn-light { background: #edf1f6; color: #1f2937; }
		table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 16px;
		}
		th, td {
			border-bottom: 1px solid var(--line);
			padding: 10px 8px;
			text-align: left;
			vertical-align: middle;
		}
		th {
			font-size: 13px;
			color: var(--muted);
			text-transform: uppercase;
			letter-spacing: 0.03em;
		}
		.acciones {
			display: flex;
			gap: 8px;
			flex-wrap: wrap;
		}
	</style>
</head>
<body>
	<div class="contenedor">
		<div class="panel">
			<h1>Gestionar Bloque de Horario</h1>
			<p class="sub">CU5 - <?php echo htmlspecialchars($this->tituloAccion); ?></p>

			<?php if ($msg !== ''): ?>
				<div class="mensaje" style="background: <?php echo htmlspecialchars($this->colorAlerta); ?>;"><?php echo htmlspecialchars($msg); ?></div>
			<?php endif; ?>

			<form method="post" action="index.php?accion_usuario=gestion_bloquehorario">
				<input type="hidden" name="accion_bloque" value="<?php echo htmlspecialchars($accionGuardar); ?>" />
				<input type="hidden" name="id_bloque" value="<?php echo $this->idSel; ?>" />

				<div class="form-grid">
					<div>
						<label for="txtHoraInicio">txtHoraInicio</label>
						<input type="time" id="txtHoraInicio" name="hora_inicio" required value="<?php echo htmlspecialchars($this->iniSel); ?>" />
					</div>
					<div>
						<label for="txtHoraFin">txtHoraFin</label>
						<input type="time" id="txtHoraFin" name="hora_fin" required value="<?php echo htmlspecialchars($this->finSel); ?>" />
					</div>
					<div>
						<label for="txtCupos">txtCupos</label>
						<input type="number" id="txtCupos" name="cantidad_cupos" min="1" required value="<?php echo $this->cuposSel; ?>" />
					</div>
					<div>
						<label for="selPeriodo">selPeriodo</label>
						<select id="selPeriodo" name="id_periodo" required>
							<option value="">Seleccione periodo</option>
							<?php foreach ($this->listaPeriodos as $periodo): ?>
								<?php
									$idPeriodo = (int)($periodo['id'] ?? 0);
									// $gestion = (string)($periodo['gestion'] ?? '');
									$semestre = (string)($periodo['semestre'] ?? '');
								?>
								<option value="<?php echo $idPeriodo; ?>" <?php echo $this->idPerSel === $idPeriodo ? 'selected' : ''; ?>><?php echo htmlspecialchars($semestre); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="form-fila">
					<div></div>
					<button id="btnGuardar" class="btn btn-primary" type="submit"><?php echo $this->modoEdicion ? 'Actualizar' : 'Guardar'; ?></button>
				</div>
			</form>

			<table>
				<thead>
					<tr>
						<th>ID</th>
						<th>Hora Inicio</th>
						<th>Hora Fin</th>
						<th>Cupos</th>
						<th>Periodo</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
					<?php if (count($this->lista) === 0): ?>
						<tr>
							<td colspan="6">No hay bloques de horario registrados.</td>
						</tr>
					<?php else: ?>
						<?php foreach ($this->lista as $bloque): ?>
							<?php
								$id = (int)($bloque['id'] ?? 0);
								$ini = (string)($bloque['hora_inicio'] ?? '');
								$fin = (string)($bloque['hora_fin'] ?? '');
								$cupos = (int)($bloque['cantidad_cupos'] ?? 0);
								$idPeriodo = (int)($bloque['idperiodo'] ?? 0);
								// $gestion = (string)($bloque['gestion'] ?? '');
								$semestre = (string)($bloque['semestre'] ?? '');
							?>
							<tr>
								<td><?php echo $id; ?></td>
								<td><?php echo htmlspecialchars($ini); ?></td>
								<td><?php echo htmlspecialchars($fin); ?></td>
								<td><?php echo $cupos; ?></td>
								<td><?php echo htmlspecialchars( $semestre); ?></td>
								<td>
									<div class="acciones">
										<form method="post" action="index.php?accion_usuario=gestion_bloquehorario" style="margin:0;">
											<input type="hidden" name="accion_bloque" value="editar" />
											<input type="hidden" name="id_bloque" value="<?php echo $id; ?>" />
											<input type="hidden" name="hora_inicio" value="<?php echo htmlspecialchars($ini); ?>" />
											<input type="hidden" name="hora_fin" value="<?php echo htmlspecialchars($fin); ?>" />
											<input type="hidden" name="cantidad_cupos" value="<?php echo $cupos; ?>" />
											<input type="hidden" name="id_periodo" value="<?php echo $idPeriodo; ?>" />
											<button class="btn btn-light" type="submit">Editar</button>
										</form>

										<form method="post" action="index.php?accion_usuario=gestion_bloquehorario" style="margin:0;" onsubmit="return confirm('Deseas eliminar este bloque de horario?');">
											<input type="hidden" name="accion_bloque" value="eliminar" />
											<input type="hidden" name="id_bloque" value="<?php echo $id; ?>" />
											<button class="btn btn-danger" type="submit">Eliminar</button>
										</form>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<form method="post" action="index.php?accion_usuario=gestion_bloquehorario" style="margin-top:14px;">
				<input type="hidden" name="accion_bloque" value="volver" />
				<button class="btn btn-light" type="submit">Volver</button>
			</form>
		</div>
	</div>
</body>
</html>
		<?php
	}
}
