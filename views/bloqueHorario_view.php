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
	public string $htmlContenidoListado;
	public string $accionEstrategia;
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
		array $listaPeriodos,
		string $htmlContenidoListado,
		string $accionEstrategia
	): void {
		$this->modoEdicion = $modoEdicion;
		$this->idSel = $idSel;
		$this->iniSel = $iniSel;
		$this->finSel = $finSel;
		$this->cuposSel = $cuposSel;
		$this->idPerSel = $idPerSel;
		$this->lista = $lista;
		$this->listaPeriodos = $listaPeriodos;
		$this->htmlContenidoListado = $htmlContenidoListado;
		$this->accionEstrategia = $accionEstrategia;
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
		$this->htmlContenidoListado = '';
		$this->accionEstrategia = 'ver_tabla';
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
		.selector-formato {
			display: flex;
			gap: 8px;
			flex-wrap: wrap;
			margin-bottom: 14px;
		}
		.btn-activo {
			background: #dbeafe;
			color: #12356b;
			border: 1px solid #9dc2f7;
		}
		.grid-horarios {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
			gap: 12px;
			margin-top: 16px;
		}
		.card-horario {
			border: 1px solid var(--line);
			border-radius: 8px;
			background: #ffffff;
			padding: 14px;
		}
		.card-horario-id {
			color: var(--muted);
			font-size: 13px;
			font-weight: 700;
			margin-bottom: 6px;
		}
		.card-horario-horas {
			font-size: 24px;
			font-weight: 700;
			margin-bottom: 10px;
		}
		.card-horario-meta {
			display: grid;
			gap: 4px;
			color: var(--muted);
			margin-bottom: 12px;
		}
		.card-horario-acciones {
			margin-top: 10px;
		}
		.listado-vacio {
			border: 1px solid var(--line);
			border-radius: 8px;
			padding: 12px;
			margin-top: 16px;
			color: var(--muted);
			background: #fcfdff;
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

			<div class="selector-formato">
				<form method="post" action="index.php?accion_usuario=gestion_bloquehorario">
					<input type="hidden" name="accion_estrategia" value="ver_tabla" />
					<button class="btn <?php echo $this->accionEstrategia === 'ver_tabla' ? 'btn-activo' : 'btn-light'; ?>" type="submit">Ver tabla</button>
				</form>
				<form method="post" action="index.php?accion_usuario=gestion_bloquehorario">
					<input type="hidden" name="accion_estrategia" value="ver_tarjetas" />
					<button class="btn <?php echo $this->accionEstrategia === 'ver_tarjetas' ? 'btn-activo' : 'btn-light'; ?>" type="submit">Ver tarjetas</button>
				</form>
			</div>

			<form method="post" action="index.php?accion_usuario=gestion_bloquehorario">
				<input type="hidden" name="accion_bloque" value="<?php echo htmlspecialchars($accionGuardar); ?>" />
				<input type="hidden" name="accion_estrategia" value="<?php echo htmlspecialchars($this->accionEstrategia); ?>" />
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

			<?php echo $this->htmlContenidoListado; ?>

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
