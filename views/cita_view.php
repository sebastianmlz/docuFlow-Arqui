<?php

class cita_view
{
	public bool $vistaPostulante;
	public bool $vistaEjecutivo;
	public int $idCitaActiva;
	public array $citaActiva = array();
	public array $listaCitasHoy;
	public array $listaDocs;
	public array $listaModulos;
	public array $listaBloques;
	public bool $recepcionAbierta;
	public int $idCitaRecepcion = 0;
	public string $tituloAccion;
	public string $colorAlerta;

	public function __construct()
	{
		$this->vistaPostulante = false;
		$this->vistaEjecutivo = false;
		$this->idCitaActiva = 0;
		$this->listaCitasHoy = array();
		$this->listaDocs = array();
		$this->listaModulos = array();
		$this->listaBloques = array();
		$this->recepcionAbierta = false;
		$this->tituloAccion = 'Gestionar Cita';
		$this->colorAlerta = '#eaf2fe';
	}

	public function sincronizar(array $datos): void
	{
		$this->vistaPostulante = (bool)($datos['vistaPostulante'] ?? false);
		$this->vistaEjecutivo = (bool)($datos['vistaEjecutivo'] ?? false);
		$this->idCitaActiva = (int)($datos['idCitaActiva'] ?? 0);
		$this->listaCitasHoy = (array)($datos['listaCitasHoy'] ?? array());
		$this->listaDocs = (array)($datos['listaDocs'] ?? array());
		$this->listaModulos = (array)($datos['listaModulos'] ?? array());
		$this->listaBloques = (array)($datos['listaBloques'] ?? array());
		$this->recepcionAbierta = (bool)($datos['recepcionAbierta'] ?? false);
		$this->citaActiva = (array)($datos['citaActiva'] ?? array());
		$this->idCitaRecepcion = (int)($datos['idCitaRecepcion'] ?? 0);
	}

	public function listar(array $lista): void
	{
		$this->listaCitasHoy = $lista;
		$this->tituloAccion = $this->vistaPostulante ? 'Gestion principal de cita' : 'Tabla principal de citas del dia';
		$this->colorAlerta = '#eaf2fe';
		$this->render('');
	}

	public function reservar(string $msg): void
	{
		$this->tituloAccion = 'Intento de reserva de cita';
		$this->colorAlerta = '#eaf2fe';
		$this->render($msg);
	}

	public function abrirRecepcion(string $msg): void
	{
		$this->tituloAccion = 'Recepcion documental del ejecutivo';
		$this->colorAlerta = '#fef9ea';
		$this->render($msg);
	}

	public function guardarRecepcion(string $msg): void
	{
		$this->tituloAccion = 'Resultado de guardado documental';
		$this->colorAlerta = '#eaf2fe';
		$this->render($msg);
	}

	private function render(string $msg): void
	{
		$ruta = $this->vistaEjecutivo ? 'gestion_cita' : 'mis_citas';
		?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>DocuFlow - Gestionar Cita</title>
	<style>
		:root {
			--bg: #f4f6f9;
			--panel: #ffffff;
			--line: #d7dfe7;
			--text: #1f2937;
			--muted: #5d6d7e;
			--primary: #0f5cc0;
			--danger: #ab1f1f;
		}
		* { box-sizing: border-box; }
		body {
			margin: 0;
			font-family: "Segoe UI", Tahoma, Arial, sans-serif;
			background: var(--bg);
			color: var(--text);
		}
		.contenedor {
			max-width: 1040px;
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
		h1 { margin: 0 0 14px; font-size: 24px; }
		.sub { margin: 0 0 16px; color: var(--muted); }
		.mensaje {
			margin-bottom: 14px;
			padding: 10px 12px;
			border-radius: 10px;
			background: #eaf2fe;
			border: 1px solid #c8daf7;
		}
		label {
			display: block;
			margin-bottom: 6px;
			font-weight: 600;
		}
		input[type="text"],
		select,
		textarea {
			width: 100%;
			padding: 10px 12px;
			border: 1px solid #c6d1dc;
			border-radius: 10px;
			font-size: 14px;
		}
		textarea { min-height: 64px; resize: vertical; }
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
			vertical-align: top;
		}
		th {
			font-size: 13px;
			color: var(--muted);
			text-transform: uppercase;
			letter-spacing: 0.03em;
		}
		.form-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 10px;
		}
		@media (max-width: 760px) {
			.form-grid { grid-template-columns: 1fr; }
		}
		.ficha {
			border: 1px solid var(--line);
			border-radius: 10px;
			padding: 14px;
			background: #f8fbff;
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
			<h1>Gestionar Cita</h1>
			<p class="sub"><?php echo htmlspecialchars($this->tituloAccion); ?></p>

			<?php if ($msg !== ''): ?>
				<div class="mensaje" style="background: <?php echo htmlspecialchars($this->colorAlerta); ?>;"><?php echo htmlspecialchars($msg); ?></div>
			<?php endif; ?>

			<?php if ($this->vistaPostulante): ?>
				<?php if ($this->idCitaActiva > 0): ?>
					<div class="ficha">
						<h2>Tu cita activa</h2>
						<p><strong>ID:</strong> <?php echo (int)($this->citaActiva['id'] ?? 0); ?></p>
						<p><strong>Fecha:</strong> <?php echo htmlspecialchars((string)($this->citaActiva['fecha'] ?? '')); ?></p>
						<p><strong>Modulo:</strong> <?php echo htmlspecialchars((string)($this->citaActiva['modulo'] ?? '')); ?></p>
						<p><strong>Bloque:</strong> <?php echo htmlspecialchars((string)($this->citaActiva['hora_inicio'] ?? '')); ?> - <?php echo htmlspecialchars((string)($this->citaActiva['hora_fin'] ?? '')); ?></p>
						<p><strong>Estado:</strong> <?php echo ((int)($this->citaActiva['estado'] ?? 0)) === 0 ? 'Pendiente' : 'Atendida'; ?></p>
					</div>
				<?php else: ?>
					<h2>Reservar cita</h2>
					<form method="post" action="index.php?accion_usuario=<?php echo htmlspecialchars($ruta); ?>">
						<input type="hidden" name="accion_cita" value="reservar" />

						<div class="form-grid">
							<div>
								<label for="selModulo">Modulo</label>
								<select id="selModulo" name="id_modulo" required>
									<option value="">Seleccione modulo</option>
									<?php foreach ($this->listaModulos as $modulo): ?>
										<?php
											$idModulo = (int)($modulo['id'] ?? 0);
											$nombreModulo = (string)($modulo['nombre'] ?? '');
										?>
										<option value="<?php echo $idModulo; ?>"><?php echo htmlspecialchars($nombreModulo); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div>
								<label for="selBloque">Bloque Horario</label>
								<select id="selBloque" name="id_bloque" required>
									<option value="">Seleccione bloque</option>
									<?php foreach ($this->listaBloques as $bloque): ?>
										<?php
											$idBloque = (int)($bloque['id'] ?? 0);
											$ini = (string)($bloque['hora_inicio'] ?? '');
											$fin = (string)($bloque['hora_fin'] ?? '');
											// $cupos = (int)($bloque['cantidad_cupos'] ?? 0);
										?>
										<option value="<?php echo $idBloque; ?>"><?php echo htmlspecialchars($ini . ' - ' . $fin ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<div class="acciones" style="margin-top:12px;">
							<button class="btn btn-primary" type="submit">Reservar</button>
						</div>
					</form>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ($this->vistaEjecutivo): ?>
				<h2>Citas de hoy</h2>
				<table>
					<thead>
						<tr>
							<th>ID</th>
							<th>Postulante</th>
							<th>Correo</th>
							<th>Modulo</th>
							<th>Bloque</th>
							<th>Estado</th>
							<th>Acciones</th>
						</tr>
					</thead>
					<tbody>
						<?php if (count($this->listaCitasHoy) === 0): ?>
							<tr>
								<td colspan="7">No hay citas para hoy.</td>
							</tr>
						<?php else: ?>
							<?php foreach ($this->listaCitasHoy as $cita): ?>
								<?php
									$id = (int)($cita['id'] ?? 0);
									$estado = (int)($cita['estado'] ?? 0);
								?>
								<tr>
									<td><?php echo $id; ?></td>
									<td><?php echo htmlspecialchars((string)($cita['postulante'] ?? '')); ?></td>
									<td><?php echo htmlspecialchars((string)($cita['correo_postulante'] ?? '')); ?></td>
									<td><?php echo htmlspecialchars((string)($cita['modulo'] ?? '')); ?></td>
									<td><?php echo htmlspecialchars((string)($cita['hora_inicio'] ?? '') . ' - ' . (string)($cita['hora_fin'] ?? '')); ?></td>
									<td><?php echo $estado === 0 ? 'Pendiente' : 'Atendida'; ?></td>
									<td>
										<?php if ($estado === 0): ?>
											<form method="post" action="index.php?accion_usuario=<?php echo htmlspecialchars($ruta); ?>" style="margin:0;">
												<input type="hidden" name="accion_cita" value="abrir_recepcion" />
												<input type="hidden" name="id_cita" value="<?php echo $id; ?>" />
												<button class="btn btn-light" type="submit">Abrir recepcion</button>
											</form>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

				<?php if ($this->recepcionAbierta && $this->idCitaRecepcion > 0): ?>
					<h3 style="margin-top:18px;">Recepcion de documentos para cita #<?php echo $this->idCitaRecepcion; ?></h3>
					<form method="post" action="index.php?accion_usuario=<?php echo htmlspecialchars($ruta); ?>">
						<input type="hidden" name="accion_cita" value="guardar_recepcion" />
						<input type="hidden" name="id_cita" value="<?php echo $this->idCitaRecepcion; ?>" />

						<table>
							<thead>
								<tr>
									<th>Documento</th>
									<th>Entrega</th>
									<th>Observacion</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($this->listaDocs as $doc): ?>
									<?php
										$idDoc = (int)($doc['id'] ?? 0);
										$nombreDoc = (string)($doc['nombre'] ?? '');
									?>
									<tr>
										<td><?php echo htmlspecialchars($nombreDoc); ?></td>
										<td><input type="checkbox" name="entrega[<?php echo $idDoc; ?>]" value="1" /></td>
										<td><textarea name="obs[<?php echo $idDoc; ?>]" placeholder="Observacion"></textarea></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

						<div class="acciones" style="margin-top:12px;">
							<button class="btn btn-primary" type="submit">Guardar recepcion</button>
						</div>
					</form>

					<form method="post" action="index.php?accion_usuario=<?php echo htmlspecialchars($ruta); ?>" style="margin-top:10px;">
						<input type="hidden" name="accion_cita" value="cancelar_recepcion" />
						<button class="btn btn-danger" type="submit">Cancelar recepcion</button>
					</form>
				<?php endif; ?>
			<?php endif; ?>

			<form method="post" action="index.php" style="margin-top:14px;">
				<input type="hidden" name="accion_usuario" value="volver_dashboard" />
				<button class="btn btn-light" type="submit">Volver</button>
			</form>
		</div>
	</div>
</body>
</html>
		<?php
	}
}
