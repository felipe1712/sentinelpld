# Atestia — Especificación de Mecanismos Automatizados de Monitoreo (notarios y corredores)
### Versión 1.0 · 19-nov-2026 · Cumple Acuerdo 115/2026 Art. 41 (obligatorio a más tardar el **1-jun-2027**, transitorio Noveno)

> **Alcance y límites.** Esta especificación define lo que el sistema hace. **No** audita (Art. 45 d: conflicto de interés), **no** da opinión legal y
> **no presenta avisos por sí mismo**: prepara el paquete y el notario lo presenta con su Firma Electrónica. El proveedor actúa como **encargado** de datos (LFPDPPP).
> **No verificado todavía:** Anexos 3 a 8 del Acuerdo (campos), estado del Anexo 12-A, fecha de la resolución de formatos, valor de la UMA vigente, formato del listado UIF, leyes estatales del notariado.
> Todo eso entra como **parámetro configurable**, nunca como valor fijo.

---

## 1. Principio de proporcionalidad (Art. 41, intro)

Los mecanismos deben ser "razonablemente adecuados al volumen, naturaleza, complejidad y Riesgo". Se ofrecen dos perfiles:

| Perfil | Para quién | Diferencias |
|---|---|---|
| **Lite** | Notarías con pocos actos de Actividad Vulnerable al mes, riesgo BAJO/MEDIO | Carga manual asistida, alertas por correo, reportes mensuales, auditoría interna (Art. 44) |
| **Pro** | Volumen alto, riesgo ALTO o varios fedatarios | Carga por API/CSV desde su sistema de protocolo, alertas en tiempo real, tableros, exportación para auditor externo (Art. 45), multiusuario con roles |

El perfil queda **documentado** en el Manual (apartado 11) como justificación de adecuación.

---

## 2. Mapa Art. 41 → módulos

| Función del Art. 41 | Módulo | Qué hace | Evidencia |
|---|---|---|---|
| I. Conservar, actualizar y consultar el expediente único | **M1 Expediente** | Ficha por cliente (PF/PM/fideicomiso), documentos, versiones, vigencias | Historial versionado, hash |
| II. Base consolidada para monitoreo y detección | **M2 Base consolidada** | Une clientes, actos, montos, pagos y contrapartes; acumulación de 6 meses | Consultas reproducibles |
| III. Proveer información a la metodología | **M3 Datos para EBR** | Agrega 12 meses de actos, clientes, geografía, transacciones, canales | Tablas de insumo por corte |
| IV. Ejecutar el modelo y conservar histórico ≥10 años | **M4 Motor de riesgo** | Calcula Grado de Riesgo y Perfil transaccional; guarda cada versión | Línea de tiempo por cliente |
| V. Alertas (ALTO, PEP, lista UIF, jurisdicciones) | **M5 Alertas** | Reglas, severidad, flujo de revisión | Bitácora de alertas |
| VI. Efectivo y metales preciosos | **M6 Monitor de efectivo** | Valida forma de pago contra Arts. 32 y 33 | Registro de pagos |
| Aviso (Ley Art. 23, 24) | **M7 Calendario y paquete de aviso** | Fecha límite, borrador de aviso, acuse | Acuse cifrado |
| Auditoría (Arts. 42-48) | **M8 Exportación para auditor** | Paquete de solo lectura | Registro de accesos |
| Capacitación y personal (Arts. 39 Bis, 39 Bis 2) | **M9 Cumplimiento interno** | Calendario, constancias, declaraciones | Registros |
| Evidencia de verificación | **M10 Conectores de verificación** | Consulta, reconsulta y versionado de reportes | Cadena de hash |

---

## 3. Modelo de datos (resumen)

```
notaria(id, rfc, titular, perfil[lite|pro], entidad_colegiada?, parametros_id)
usuario(id, notaria_id, rol[notario|coordinador|colaborador|auditor_ro], mfa)
cliente(id, notaria_id, tipo[PF|PM|FID|OTRA], datos_json, version, vigente_desde)
persona_relacionada(id, cliente_id, rol[solicitante_material|apoderado|BC|contraparte|PEP_vinculada])
acto(id, notaria_id, tipo_inciso[a|b|c|d|e], subtipo, fecha_otorgamiento, fecha_protocolizacion,
     valor_precio, valor_catastral, valor_comercial, monto_garantia, moneda, instrumento_no, estado)
acto_parte(acto_id, cliente_id, rol[adquirente|enajenante|deudor|acreedor|socio|otro], monto)
pago(id, acto_id, forma[efectivo|transferencia|cheque|otro], monto, fecha, comprobante_ref, tercero?)
grado_riesgo(id, cliente_id, valor[BAJO|MEDIO|ALTO], puntaje, factores_json, regla_forzada?, calculado_en, metodologia_ver)
perfil_transaccional(id, cliente_id, base[acto_unico|estimado_6m|historico], monto_max_mensual, calculado_en)
alerta(id, acto_id?, cliente_id, regla_id, severidad, estado, analisis, decision, revisor, fecha)
aviso(id, acto_ids[], tipo[mensual|24h], canal[fiscal|portal|entidad_colegiada], fecha_limite, estado, acuse_cifrado)
evidencia_verificacion(proveedor,id, cliente_id, reporte_id, version, hash_prev, hash, fecha)
bitacora_acceso(id, usuario_id, objeto, accion, ts, ip)
parametro(clave, valor, vigente_desde, fuente)   -- UMA, umbrales, plazos, listas
```
Los **umbrales y la UMA** viven en `parametro` con vigencia y fuente; nunca en código.

---

## 4. Reglas por módulo

### M1 Expediente
- Plantillas por tipo de cliente; los campos de Anexos 3 a 8 se cargan como **esquema versionable** (`esquema_anexo`), hoy provisional.
- Validación de completitud: no se puede cerrar acto con expediente incompleto sin **excepción firmada** del notario o registro de **abstención** (Ley Art. 21).
- Solicitante material identificado al momento del acto (Reglas Art. 13).
- Declaración de Beneficiario Controlador con prelación 25% → control por otros medios → alta dirección; fideicomisos ascienden la cadena (Arts. 23 Quinquies y 23 Quinquies 1); excepciones del 23 Quinquies 2.
- Revisión anual obligatoria de expedientes con relación de negocios (Reglas Art. 21); comprobante de domicilio con antigüedad ≤ 3 meses; el sistema rechaza nombres ficticios o confidenciales (Art. 23).
- Vigencia y recordatorios de actualización; cada cambio crea versión nueva (no sobrescribe).

### M2 Base consolidada y acumulación
- Llave de acumulación: `(cliente, tipo_inciso)`; ventana de **6 meses desde el primer acto**.
- **No acumulan** incisos **b)** y **e)** del Apartado A (siempre aviso).
- Suma sin contribuciones ni accesorios (Reglamento Art. 6).
- Cuando suma ≥ umbral del tipo: alerta `ACUMULACION_UMBRAL` y propuesta de aviso en el acto que lo alcanza.
- Fecha del acto = otorgamiento o protocolización (Reglamento Art. 5). Los actos desde el **17-jul-2025** usan los umbrales nuevos (Reglamento, transitorio Sexto fr. IV).

### M3 Insumos de EBR
Genera, para cualquier corte de 12 meses, las tablas por elemento (actos, clientes, geografía, transacciones, canales) que alimentan la metodología del documento 1 §4.
Si la notaría tiene menos de 12 meses, marca `datos_proyectados=true`. Reporta inconsistencias entre datos y mecanismos.

### M4 Motor de riesgo
- Implementa las fórmulas del documento 1 §4.4 (indicador 0-3; dato faltante = 2; mitigantes ≤ 0.30; bandas BAJO <35, MEDIO 35-64, ALTO ≥65).
- **Reglas duras** (no se pueden bajar): PEP extranjera, jurisdicción de lista UIF, coincidencia en listado, pago en efectivo prohibido, negativa a informar.
- Recalcula: alta, cada **6 meses** (más frecuente si ALTO, parámetro), y por evento (nuevo acto, cambio de datos, coincidencia nueva).
- Cada cálculo guarda factores, puntaje, versión de metodología y quién/qué lo disparó. Retención **≥ 10 años**.
- Perfil transaccional: cliente de un solo acto = perfil derivado del acto; recurrente = monto máximo mensual estimado 6 meses, luego histórico.
- Cualquier cambio de metodología crea `metodologia_ver` nueva y no reescribe resultados anteriores.

### M5 Alertas (catálogo inicial)
| ID | Regla | Severidad |
|---|---|---|
| A01 | Cliente Grado ALTO inicia acto | Alta |
| A02 | Cliente o vinculado PEP (extranjera = ALTO forzado) | Alta |
| A03 | Coincidencia en listado UIF | Crítica (24h) |
| A04 | Jurisdicción en lista UIF | Alta |
| A05 | Acto fuera del perfil transaccional | Media |
| A06 | Monto entre 80% y 99% del umbral (posible fraccionamiento) | Media |
| A07 | Acumulación alcanza umbral | Alta |
| A08 | Pago en efectivo en supuesto del Art. 32 | Crítica |
| A09 | Forma de pago no identificada en acto ≥ 8,025 UMA (Art. 33) | Alta |
| A10 | Precio vs valor catastral/avalúo con brecha > {{param}}% | Media |
| A11 | Reventa del mismo inmueble en < {{param}} meses | Media |
| A12 | Pago por tercero no vinculado | Media |
| A13 | Secuencia constitución → aumento de capital → venta de acciones en < {{param}} días | Alta |
| A14 | Poder irrevocable de dominio o administración | Informativa (siempre aviso) |
| A15 | Mutuo con acreedor no financiero | Informativa (siempre aviso) |
| A16 | Beneficiario Controlador no identificado o incompleto | Alta |
| A17 | Reevaluación de grado vencida (> 6 meses) | Media |
| A18 | Cliente con información inconsistente entre fuentes | Media |

Flujo: `nueva → en revisión → descartada | escalada al notario | aviso`; cada cierre exige **análisis escrito** y queda inmutable. SLA configurable (por defecto 2 días hábiles; críticas: mismo día).
Las reglas A10-A13 son **indicadores propios** de la metodología (no normativos); se marcan [ORPI] en su catálogo.

### M6 Monitor de efectivo (Ley Arts. 32 y 33)
- Tabla de supuestos como **parámetros** (UMA × múltiplo, por tipo de bien o acto): fr. I inmuebles ≥ 8,025 UMA; fr. II vehículos, III relojes/joyería/metales/arte, IV boletos y premios, V blindaje, VI acciones y partes sociales, VII uso o goce y VIII consignación: ≥ 3,210 UMA (texto verificado contra la Ley, reforma DOF 16-jul-2025; el valor de la UMA sí se carga como parámetro).
- Si `forma = efectivo` y el acto cae en supuesto: **bloquea el cierre**, genera A08, registra advertencia al notario y deja constancia de abstención o aclaración.
- En actos ≥ 8,025 UMA exige forma de pago y comprobante (Art. 33); si es menor y se pagó antes de la firma, admite declaración bajo protesta.

### M7 Calendario y paquete de aviso
- Aviso mensual: fecha límite = **día 17 del mes siguiente** al del acto. Tablero con cuenta regresiva y recordatorios a 10, 5 y 1 día.
- Paquete por acto: datos del sujeto obligado, del cliente y BC, actividad u ocupación, descripción del acto, montos totales pagados (sin contribuciones para umbral). Salida: **borrador estructurado + PDF de captura**, listo para el canal.
- **Canales:** inciso a) → sistema fiscal del SAT; demás → portal UIF/SAT; o Entidad Colegiada si hay convenio. El sistema **no afirma que presentó**: el notario registra folio y acuse (cifrado, con su llave).
- **Aviso 24 h:** al cerrar A03 o una alerta marcada sospechosa, abre cuenta de 24 h. Se habilita según el parámetro `formatos_24h_vigente_desde` (6 meses después de la resolución de formatos, transitorio Quinto); antes de esa fecha el módulo solo registra la determinación.
- **Candado Sexto fr. III (Anexo 12-A):** parámetro `anexo_12a_actualizado` (sí/no/desconocido).
  - *Sí:* flujo normal para protocolizaciones de Apartados A y B.
  - *No/desconocido:* el sistema **captura y calcula igual**, marca el paquete como `PENDIENTE_DE_FORMATO` y muestra la opción elegida por el notario con su asesor (avisar con el formato vigente o esperar). Nunca se pierde el cómputo del plazo.
- Reportes de avisos por periodo y estado; cierre mensual.

### M8 Exportación para auditoría
- Rol `auditor_ro` con acceso temporal, solo lectura, a: expedientes muestreados, histórico de grados, alertas, bitácora y metodología.
- Dictamen Art. 47: el sistema ofrece **plantilla de insumos** (volumen, muestreo, hallazgos), pero **el dictamen lo emite el auditor**.
- Para ALTO: auditor externo con certificación UIF; el proveedor **no ofrece ese servicio** y solo lo refiere (independencia, Art. 45 d).

### M9 Cumplimiento interno
- Calendario de capacitación anual (primer periodo **2027**), registros F-08, evaluación y constancias; instructor con 5 años de experiencia (campo de acreditación).
- Declaraciones de personal F-07 para **nuevas contrataciones desde 1-mar-2027**.
- Recordatorios de revisión anual de Manual y metodología (≤ 12 meses), actas F-10.

### M10 Conectores de verificación (opcionales e intercambiables)
- El producto **funciona sin ningún conector**: el notario puede capturar a mano el resultado de sus consultas con fuente y fecha.
- Interfaz única `ConectorVerificacion` (consultar, reconsultar, versionar); AvalID es **un** conector posible, contratado por separado, igual que cualquier otro proveedor. No hay datos compartidos entre productos.
- Consulta de verificación (identidad, listas, PEP, medios públicos) por cliente y contraparte, con **fuente, fecha y hash**.
- **Reconsulta** automática por calendario y por evento; versionado y `diff_estructurado` según la spec de reconsulta.
- Resultados alimentan alertas A02, A03, A18. Los reportes **nunca mencionan avisos**.
- La **lista de la UIF es reservada**: el producto no la solicita ni la comparte; si el notario la recibe, la carga él mismo en `lista_uif_local` (cifrada, solo comparación).

---

## 5. Seguridad y confidencialidad (Ley Arts. 38, 63 fr. II, 22)

| Control | Implementación |
|---|---|
| Aislamiento | Un inquilino (tenant) por notaría; cifrado por inquilino |
| Acuses y avisos | Cifrados con **llave del notario** (KMS por cliente); el personal del proveedor no los puede leer |
| Acceso mínimo | Roles; el colaborador no ve avisos; el auditor solo lo exportado |
| Autenticación | MFA obligatorio para notario y coordinador |
| Trazabilidad | Bitácora de accesos inmutable (≥ 10 años) |
| Cliente-aviso | Ninguna pantalla, correo o API hacia el cliente menciona avisos; plantillas de comunicación revisadas |
| Retención | 10 años desde el acto; borrado solo por vencimiento y sin retención legal |
| Respaldos | Cifrados, restauración probada trimestralmente |
| Proveedores | Contrato de encargo y confidencialidad; subencargados listados |
| Incidentes | Plan de respuesta y notificación al responsable (notario) |

---

## 6. Integraciones

| Tipo | Detalle |
|---|---|
| Carga | CSV/Excel plantilla, API REST (`POST /actos`, `/clientes`, `/pagos`), conector a sistemas de protocolo (fase 2) |
| Salida | Webhooks (`alerta.creada`, `aviso.por_vencer`, `grado.cambio`), API de consulta, exportación PDF/CSV |
| Identidad | Firma electrónica del notario para constancias (F-06) y cuestionarios (F-05) |
| Externas | Servicios de verificación que el propio cliente contrate (conectores); **sin** automatización de portales fiscales |

---

## 7. Casos de prueba de aceptación

| # | Caso | Resultado esperado |
|---|---|---|
| T01 | Compraventa inmueble con valor catastral mayor que precio, ≥ 8,000 UMA | Umbral con el valor más alto; aviso a día 17 |
| T02 | Dos compraventas del mismo cliente en 4 meses que suman ≥ umbral | A07 en el segundo; aviso en ese acto |
| T03 | Dos poderes irrevocables del mismo cliente | Dos avisos; sin acumulación |
| T04 | Pago de $X en efectivo en inmueble ≥ 8,025 UMA | Bloqueo + A08 |
| T05 | Cliente PEP extranjera | ALTO forzado, A02, constancia requerida |
| T06 | Coincidencia en lista local UIF | A03, abre 24 h (si formato vigente) |
| T07 | Cliente de un acto con perfil derivado del acto | Perfil `acto_unico` |
| T08 | Acto el 31-ene con aviso | Fecha límite 17-feb |
| T09 | Cambio de metodología | Nueva versión; resultados previos intactos |
| T10 | Acto con `anexo_12a_actualizado=desconocido` | Paquete `PENDIENTE_DE_FORMATO`; plazo calculado |
| T11 | Auditor accede | Solo lectura, registro de acceso |
| T12 | Intento de ver acuse por colaborador | Denegado y registrado |
| T13 | Sin 12 meses de datos | Marca `datos_proyectados` |
| T14 | Fideicomiso cliente | Asciende cadena hasta BC |
| T15 | Dato faltante en indicador | Puntúa 2 y alerta de completitud |

---

## 8. Fases

| Fase | Contenido | Meta |
|---|---|---|
| 0 | Parámetros, esquema de datos, Anexos 3-8 cargados | Cuando se obtengan los Anexos |
| 1 | M1, M2, M7 (calendario), M6, bitácora y seguridad | Piloto con 2-3 notarías |
| 2 | M4, M5, M10 | Antes de 1-mar-2027 (Caps. III Bis, Ter y Quinquies aplican a actos desde esa fecha) |
| 3 | M3, M8, M9, Pro (API) | **1-jun-2027** (mecanismos operando) |
| 4 | Consulta PEP guiada (~30-ago-2027), conector a sistemas de protocolo | Posterior |

---

## 9. Pendientes que condicionan la construcción
1. Obtener Anexos 3 a 8 del Acuerdo y modelar sus campos.
2. Confirmar estado del **Anexo 12-A** y de la resolución de formatos (fecha).
3. Valor UMA y verificación literal de los supuestos de los Arts. 32 y 33.
4. Formato de entrega del listado UIF.
5. Leyes estatales del notariado (conflictos, supervisión, entidades colegiadas).
6. Revisión legal externa (abogado PLD) antes de comercializar.

---

## 10. Parametrización por apartado (notarios y corredores)

El motor es único; cada tipo de fedatario es un **perfil de reglas** (`perfil_apartado`) con catálogo de actos, umbrales, canal de aviso y obligaciones.

| Perfil | Catálogo | Umbral / aviso | Acumulación | Estado |
|---|---|---|---|---|
| **A · Notarios** | Art. 17 fr. XII A a)-e) | a) ≥ 8,000 UMA; b), c), e) siempre; d) ≥ 4,000 UMA | a), c), d) | Completo (docs 02, 03) |
| **B · Corredores** | Art. 17 fr. XII B a)-d) | a) avalúos ≥ 8,025 UMA con fe pública (Reglamento Art. 29); b)-d) por acto, umbral por confirmar | **Ninguna** | Parametrizado (doc 04) |

Reglas: (1) un tenant puede activar los dos perfiles; (2) anexos de identificación y formatos de aviso son esquemas versionables por perfil; (3) un perfil sin formato oficial publicado queda en `captura y cómputo`; (4) correduría persona moral activa el rol Representante Encargado.
