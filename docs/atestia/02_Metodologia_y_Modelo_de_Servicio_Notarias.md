# SISTEMA PLD PARA NOTARÍAS — PROYECTO Y METODOLOGÍA PROPIA (v1)
### Documento interno del producto Atestia. Los apartados marcados [INTERNO] se quitan antes de enseñarlo a un cliente.
### Es el módulo **Notarías (Apartado A)**. Corredores públicos, en el documento 04.

---

## 0. NOTA DE VERIFICACIÓN (léela antes de usar este documento)

**Confirmado con texto oficial que tengo en el expediente** (Ley compilada con última reforma DOF 16-jul-2025,
Reglamento compilado con reforma DOF 27-mar-2026, Acuerdo 115/2026 con sus 12 transitorios):
- Arts. 17 fr. XII, 18 a 24, 26 a 31, 32, 33, 33 Bis, 58 de la Ley.
- Arts. 5, 6, 7 del Reglamento, y el párrafo sobre avisos de inmuebles por el sistema fiscal.
- Arts. 10 Septies a 10 Septies 6, 13, 13 Bis, 19, 23 Bis a 23 Ter 5, 26 Bis a 27, 34 Bis, 34 Ter, 37 a 37 Bis 3,
  39 Bis a 39 Bis 2, 41 a 48 del Acuerdo, y sus transitorios.

**No pude verificar:**
1. Los **Anexos 3 a 8** del Acuerdo (datos exactos del expediente de identificación): la copia que leí solo
   trae los Anexos 1 y 2. El campo por campo del expediente queda **pendiente**.
2. **Leyes del notariado de cada estado**, su régimen de secreto, sus sanciones y quién supervisa la fe pública
   (el Art. 58 de la Ley remite a "la autoridad competente para supervisar el ejercicio de la fe pública").
3. La **Evaluación Nacional de Riesgos** y las guías de la UIF (Art. 10 Septies 2 y 5): la metodología debe
   tomarlas en cuenta cuando la UIF las dé a conocer. No las he leído.
4. El **valor diario de la UMA vigente** y la **lista de jurisdicciones** de la UIF: el sistema las toma como
   parámetros, no las codifica.

**Lo que es diseño propio** (no viene de la norma): ponderaciones, rangos de puntaje, indicadores de alerta,
tiempos internos y la arquitectura. Está marcado como "propuesta" y se calibra con datos reales.

---

## 1. EL PROYECTO EN UNA PÁGINA

**Producto:** *Atestia · módulo Notarías*, una solución integral de cumplimiento para el notario titular como
sujeto obligado de la LFPIORPI.

**Cuatro entregables, que son lo que la norma le exige:**
| # | Entregable | Obligación que atiende |
|---|---|---|
| 1 | **Metodología de evaluación de riesgos** de la notaría y del cliente | Ley Art. 18 fr. VII; Acuerdo Caps. II Quáter y III Bis |
| 2 | **Manual de Políticas Internas** a la medida | Ley Art. 18 fr. VIII; Acuerdo Arts. 37 y 37 Bis |
| 3 | **Mecanismos automatizados** de seguimiento, acumulación, alertas y monitoreo de efectivo | Ley Art. 18 fr. X; Acuerdo Art. 41 |
| 4 | **Capa de evidencia por persona** (conector de verificación): identidad, PEP, listas, Beneficiario Controlador, reconsulta | Acuerdo Arts. 11 a 23 Quinquies |

**Lo que el producto NO hace** (y debe decirse en la venta):
- No presenta los avisos por el notario. Los avisos de inmuebles **deben** presentarse por el sistema fiscal
  del SAT; el sistema prepara el paquete de datos y vigila los plazos.
- No dictamina la auditoría. Si el proveedor diseña metodología, manual y mecanismos, **no puede ser el auditor**:
  el Art. 45 inciso d) del Acuerdo exige no haber prestado servicios previos que generen conflicto de interés.
- No da opinión legal. Los textos se entregan para revisión del notario y de su asesor jurídico.

**Por qué ahora:**
| Fecha (confirmada en los transitorios) | Qué pasa |
|---|---|
| 17-jul-2025 | Nuevos umbrales y supuestos de aviso para el Apartado A, incisos a), c) y d): se consideran desde los actos realizados el 17-jul-2025 (decreto del Reglamento, transitorio Sexto, fr. IV). **Ojo:** la fr. III del mismo transitorio sujeta la obligación de avisar protocolizaciones de los Apartados A y B a que se **actualice el Anexo 12-A** de los formatos oficiales. No verifiqué si ya se actualizó |
| 30-nov-2026 | Entra en vigor el Acuerdo 115/2026 |
| 1-ene-2027 | Arranca el primer periodo anual de capacitación (1-ene a 31-dic-2027) |
| **1-mar-2027** | Evaluación de riesgos a disposición de la autoridad; Manual con metodología; grados de riesgo, perfil transaccional y Beneficiario Controlador para actos desde esa fecha |
| 1-jun-2027 | Mecanismos automatizados operando |
| ~30-ago-2027 | Consulta PEP 2.0 disponible |
| Ene a dic 2028 | Primer periodo de auditoría |

Hoy es 19-nov-2026: quedan unos **tres meses y medio** para el hito de 1-mar-2027. Ese es el gancho de venta.

---

## 2. QUIÉN ES EL SUJETO OBLIGADO EN UNA NOTARÍA

- La **notaría no es persona moral**: el sujeto obligado es el **notario titular, persona física**.
  Ley Art. 20, último párrafo: las personas físicas "cumplirán personal y directamente" con las obligaciones.
- Consecuencias de diseño:
  - **No hay Representante Encargado de Cumplimiento** (el Art. 20 lo exige a personas morales). El Manual
    nombra a un **coordinador interno** de apoyo, pero la responsabilidad legal sigue siendo del notario.
  - **Aprobación de directivo para PEP de alto riesgo** (Acuerdo Art. 23 Ter 5): el notario la sustituye con
    una **constancia escrita** de los motivos para realizar el acto.
  - **Certificación UIF**: el Art. 34 Bis la ofrece a personas físicas que realizan Actividades Vulnerables;
    es opcional ("podrán obtener").
  - **Dictamen de auditoría** se presenta "directamente a la persona física" (Art. 42).
- El Apartado B (corredores públicos) se trata en el doc 04. Los Apartados C y D quedan fuera del producto.

**Medios de presentación de avisos:** el notario puede presentar por sí o por conducto de una **Entidad
Colegiada** con convenio con la Secretaría (Ley Arts. 26 a 31). Esto abre el canal de venta **Colegio de
Notarios**.

---

## 3. MAPA DE ACTOS: QUÉ OBLIGA CADA ACTO NOTARIAL (Ley Art. 17 fr. XII, Apartado A)

| Inciso | Acto | ¿Aviso? | Umbral de aviso | ¿Acumulación a 6 meses? (Acuerdo Art. 19) |
|---|---|---|---|---|
| a) | Transmisión o constitución de derechos reales sobre inmuebles (se exceptúan garantías a favor de entidades del sistema financiero u organismos públicos de vivienda) | Sí, si alcanza el umbral | **≥ 8,000 UMA**, tomando el **más alto** entre precio pactado, valor catastral, valor comercial o monto garantizado por suerte principal | Sí |
| b) | Poderes irrevocables para actos de administración o dominio | **Siempre** | Ninguno | **No** |
| c) | Constitución de personas morales, aumento o disminución de capital, fusión, escisión, compraventa de acciones y partes sociales | **Siempre** | Ninguno | Sí |
| d) | Constitución o modificación de fideicomisos traslativos de dominio o de garantía (salvo a favor de entidades financieras u organismos de vivienda) | Sí, si alcanza el umbral | **≥ 4,000 UMA** | Sí |
| e) | Contratos de mutuo o crédito donde el acreedor no sea entidad financiera ni organismo de vivienda | **Siempre** | Ninguno | **No** |

Reglas que aplican a todos los actos:
- **Fecha del acto** para el aviso: la del **otorgamiento del instrumento público o la protocolización**
  (Reglamento Art. 5, segundo párrafo).
- **Plazo del aviso:** a más tardar el **día 17 del mes siguiente** (Ley Art. 23).
- **Candado de formatos (decreto del Reglamento, transitorio Sexto, fr. III):** la obligación de presentar avisos
  sobre protocolizaciones y actos que las formalicen (Apartados A y B) "estará sujeta" a que se actualice el
  **Anexo 12-A** de la Resolución de formatos. **Pendiente de verificar** si ya se actualizó y desde cuándo. Mientras
  no se aclare, el Manual debe prever las **dos situaciones** (avisar con el formato vigente o esperar el
  formato actualizado) y el sistema debe capturar y calcular **desde ya** para no perder el plazo. Esta es la
  primera consulta que debe hacerse al abogado PLD.
- **Medio:** los avisos del inciso a) **se presentan por el sistema fiscal** del SAT, con los datos del Art. 24 de
  la Ley (Reglamento, párrafo adicionado/reformado DOF 27-mar-2026; Ley Art. 24, último párrafo).
- **Monto:** para el umbral **no se cuentan contribuciones y accesorios**; al presentar el aviso se reportan
  los montos totales pagados (Reglamento Art. 6).
- **Montos inferiores** no generan obligación, salvo acumulación en seis meses (Ley Art. 17, penúltimo párrafo).
- **Identificación:** se identifica a **quien solicita materialmente** la protocolización, al momento del acto
  (Acuerdo Art. 13). Cuando el servicio de fe pública se presta al Poder Judicial o a autoridades
  administrativas en funciones de justicia, el expediente se integra solo con el Anexo 7 Bis (Art. 13 Bis).
- **Forma de pago:** en actos de los supuestos del Art. 32 por **≥ 8,025 UMA**, el notario debe **identificar la
  forma de pago**; si el valor es menor y ya se pagó antes de la firma, basta declaración bajo protesta
  (Ley Art. 33).
- **Efectivo prohibido** en los supuestos del Art. 32 (inmuebles ≥ 8,025 UMA; acciones y partes sociales
  ≥ 3,210 UMA; vehículos, joyería, arrendamientos y otros ≥ 3,210 UMA).
- **Negativa a dar información:** el notario "deberá abstenerse, sin responsabilidad alguna" del acto
  (Ley Art. 21).
- **Hechos sospechosos:** aviso en **24 horas** (Acuerdo Arts. 26 Bis, 26 Bis 1, 26 Bis 2 y 27), aun sin umbral
  o sin acto celebrado. Su envío arranca **seis meses después** de que entre en vigor la resolución de formatos
  (Acuerdo, transitorio Quinto). Esa fecha no está verificada.
- **Sanciones al notario:** además de las multas de la Ley, la Secretaría informa a la autoridad supervisora de
  la fe pública si hay "notorias deficiencias" (reincidencia en el Art. 53 frs. I a V, o violación de las frs. VI
  y VII), para que instaure el procedimiento sancionador (Ley Art. 58).

---

## 4. METODOLOGÍA PROPIA DE EVALUACIÓN DE RIESGOS

### 4.1 Estructura (lo que exige la norma, Acuerdo Arts. 10 Septies a 10 Septies 3)
1. **Elementos mínimos de riesgo:** (a) actos u operaciones, (b) tipo de clientes, (c) países y áreas
   geográficas, (d) transacciones y canales de envío o distribución.
2. **Indicadores** para cada elemento, con un valor consistente según su importancia; y un valor a cada
   elemento.
3. **Mitigantes** implementados (políticas, criterios y procedimientos del Manual) y su efecto en los
   indicadores y elementos.
4. **Indicadores específicos** de los delitos de los **Arts. 139 Quáter y 400 Bis del Código Penal Federal**
   en **cada** uno de los cuatro elementos.
5. **Datos de al menos 12 meses** (clientes, actos y montos). Notaría nueva: metodología inicial con **datos
   proyectados** y actualización a los 12 meses.
6. **Congruencia** con lo que haya en los mecanismos automatizados: no puede haber inconsistencias.
7. **Cambios:** si salen riesgos mayores o nuevos, modificar políticas dentro de **12 meses** desde que haya
   resultados claramente identificados (indicando año y mes).
8. **Revisión** por nuevos riesgos, por actualización de la Evaluación Nacional de Riesgos o a más tardar a
   los 12 meses; **por escrito** y a disposición del SAT; conservar **10 años**.
9. **Evaluación previa** antes de poner a disposición nuevos medios, canales, modalidades, productos o
   servicios, o de dirigirse a nuevos tipos de clientes (por ejemplo, firma a distancia, nuevos tipos de acto).

### 4.2 Dos niveles de evaluación
| Nivel | Qué mide | Frecuencia | Resultado |
|---|---|---|---|
| **A. Riesgo de la notaría** (institucional) | Exposición agregada de la notaría con 12 meses de datos | Anual, y por evento | BAJO / MEDIO / ALTO. Define el tipo de auditoría (Arts. 44 y 45) |
| **B. Grado de Riesgo del cliente** | Riesgo de cada cliente en cada relación | Inicial al abrir expediente; **al menos cada 6 meses**; más seguido si es ALTO (Art. 23 Bis 1) | BAJO / MEDIO / ALTO |

### 4.3 Indicadores y valores (propuesta inicial, a calibrar)
Cada indicador se califica **0 = bajo, 1 = medio, 2 = alto, 3 = crítico**. Etiqueta: **[ORPI]** = relacionado con
el Art. 400 Bis CPF; **[FT]** = relacionado con el Art. 139 Quáter CPF. **Peso** = importancia dentro del elemento.

**ELEMENTO 1. ACTOS U OPERACIONES (peso del elemento: 25%)**
| Indicador | Peso | 0 | 1 | 2 | 3 |
|---|---|---|---|---|---|
| Tipo de acto [ORPI] | 25 | Inmueble < 8,000 UMA sin otros factores | Inmueble ≥ 8,000 UMA | Constitución o compraventa de acciones; fideicomiso ≥ 4,000 UMA | Poder irrevocable; mutuo con acreedor no financiero |
| Valor del acto [ORPI] | 20 | < 4,000 UMA | 4,000 a < 8,000 | 8,000 a < 20,000 | ≥ 20,000 (parámetro) |
| Coherencia precio vs. valor catastral o de avalúo [ORPI] | 20 | Diferencia ≤ 20% | 20% a 40% | 40% a 60% | > 60% o precio muy inferior al catastral |
| Reventa o secuencia rápida del mismo bien o sociedad [ORPI] | 15 | Sin antecedentes | Más de 24 meses | 12 a 24 meses | < 12 meses con incremento no explicado |
| Estructura compleja (varios actos encadenados, cesión de derechos, aportaciones en especie) [ORPI] | 10 | No | Una capa | Dos capas | Tres o más, o sin propósito económico claro |
| Acto vinculado a financiamiento de asociaciones, donativos o causas no explicadas [FT] | 10 | No | — | Indicios | Coincidencia o indicio sólido |

**ELEMENTO 2. TIPO DE CLIENTES (peso: 35%)**
| Indicador | Peso | 0 | 1 | 2 | 3 |
|---|---|---|---|---|---|
| Tipo de persona y antigüedad [ORPI] | 15 | PF con actividad conocida o PM con más de 3 años | PM de 1 a 3 años | PM menor de 1 año con acto relevante | PM de reciente creación, sin actividad, con acto de valor alto |
| PEP [ORPI] | 20 | No | Familiar o asociado de PEP | **PEP nacional** | **PEP extranjera** |
| Beneficiario Controlador [ORPI] | 15 | Identificado y verificado | Identificado, no verificado | Estructura con una capa intermedia | No identificado o estructura opaca |
| Actividad u ocupación / origen de recursos [ORPI] | 15 | Consistente con el acto | Parcialmente documentada | Poco consistente | Inconsistente o sin explicación |
| Antecedentes y listas (sanciones, 69-B del CFF, listas UIF) [ORPI][FT] | 20 | Sin coincidencias | Homonimia descartada | Coincidencia parcial sin descartar | Coincidencia confirmada |
| Conducta durante el trámite (apuro injustificado, cambios de instrucciones, terceros que dirigen el acto) [ORPI] | 10 | Normal | Leve | Reiterada | Evasiva o intenta evitar el aviso |
| Representación por apoderado o tercero sin relación clara [ORPI] | 5 | No | Con poder y relación clara | Relación no clara | Sin relación explicable |

**ELEMENTO 3. PAÍSES Y ÁREAS GEOGRÁFICAS (peso: 20%)**
| Indicador | Peso | 0 | 1 | 2 | 3 |
|---|---|---|---|---|---|
| Residencia del cliente [ORPI] | 30 | Local | Otro estado | Extranjero sin riesgo listado | Extranjero ligado a jurisdicción de la lista UIF |
| Nacionalidad [ORPI] | 15 | Mexicana | Extranjera residente | Extranjera no residente | Nacionalidad de jurisdicción listada |
| Ubicación del bien o domicilio social [ORPI] | 25 | Zona sin alertas | Zona con alerta local | Zona con alerta sectorial | Zona señalada por la Evaluación Nacional de Riesgos (cuando la UIF la dé a conocer) |
| Origen o destino de fondos [ORPI] | 20 | Nacional | Nacional, de tercero | Extranjero | Extranjero de jurisdicción listada |
| Vínculo con jurisdicciones de alto riesgo en materia de terrorismo [FT] | 10 | No | — | Indicio | Vínculo confirmado |

**ELEMENTO 4. TRANSACCIONES Y CANALES (peso: 20%)**
| Indicador | Peso | 0 | 1 | 2 | 3 |
|---|---|---|---|---|---|
| Forma de pago [ORPI] | 30 | Transferencia desde cuenta del cliente | Cheque de caja o crédito de entidad financiera | Pagos de terceros o fraccionados | **Efectivo en supuesto del Art. 32** (se activa regla dura) |
| Pagos de terceros no vinculados [ORPI] | 20 | Ninguno | Familiar | Tercero con relación débil | Tercero sin relación |
| Fraccionamiento cerca del umbral (estructuración) [ORPI] | 20 | No | Un caso | Varios actos justo debajo del umbral en 6 meses | Patrón sistemático |
| Canal de contratación (presencial, firma a distancia, por apoderado) [ORPI] | 15 | Presencial con cliente | Presencial con apoderado | A distancia con identificación fuerte | A distancia con identificación débil |
| Uso de cuentas o flujos con jurisdicciones de FT [FT] | 15 | No | — | Indicio | Confirmado |

### 4.4 Cálculo (propuesta)
```
Inherente_e   = Σ ( valor_i × peso_i ) / (3 × Σ peso_i)  × 100        para cada elemento e (0 a 100)
Residual_e    = Inherente_e × (1 − M_e)                              M_e = eficacia de mitigantes, máximo 0.30
Grado_cliente = 0.25·Residual_Actos + 0.35·Residual_Clientes + 0.20·Residual_Geografía + 0.20·Residual_Canales
```
- Si faltan datos de un indicador se califica **2 (alto)** y se anota "dato no obtenido". Nunca 0.
- **Mitigantes:** cada uno se registra con su fuente (por ejemplo, "verificación de identidad contra INE", "constatación de forma de pago con comprobante", "consulta de listas"), y su eficacia
  (0, 0.10, 0.20, 0.30) la **justifica el notario por escrito** y se revisa en la evaluación anual. Sin
  evidencia de aplicación efectiva (Art. 10 Septies 1 fr. III exige considerar "su efectiva aplicación") el
  mitigante vale 0.
- **Grados (propuesta):** BAJO < 35 · MEDIO 35 a 64 · ALTO ≥ 65.

### 4.5 Reglas duras (anulan el puntaje; vienen de la norma)
| Condición | Efecto | Fuente |
|---|---|---|
| PEP extranjera | Grado **ALTO** | Acuerdo Art. 23 Bis 4 |
| No residente vinculado a jurisdicción de la lista UIF | Grado **ALTO** y se asientan las razones de celebrar el acto en México | Acuerdo Art. 23 Bis 4 |
| PEP nacional | Se agregan factores de riesgo adicionales sobre congruencia entre comportamiento transaccional e ingresos, funciones y nivel (**mínimo MEDIO** como criterio propio) | Acuerdo Art. 23 Bis 3 |
| PEP + ALTO | **Constancia escrita del notario** con los motivos | Acuerdo Art. 23 Ter 5 |
| Persona en el listado de la UIF (Art. 38 del Acuerdo) | Grado **ALTO** y **aviso de 24 horas** cuando la fecha de formatos lo permita | Acuerdo Art. 27 |
| Pago en efectivo en supuesto del Art. 32 | **No se acepta**; el instrumento debe consignar forma de pago | Ley Arts. 32 y 33 |
| El cliente no entrega información o documentos | **Abstención** del acto sin responsabilidad | Ley Art. 21 |
| Grado ALTO | Cuestionario ampliado con **firma electrónica**, más información sobre actividad preponderante, medidas reforzadas sobre origen y destino de recursos, datos de cónyuge y dependientes y sociedades vinculadas (PF); mayor información de accionistas y consulta a registros de la Secretaría de Economía (PM) | Acuerdo Arts. 23 Ter 3 y 23 Ter 4 |

### 4.6 Perfil transaccional en una notaría (adaptado a su realidad)
- La mayoría de los clientes son de **un solo acto**: el Art. 23 Ter 1 dice que su perfil se integra con la
  información que dan para ese acto. No se les inventa un perfil mensual.
- **Clientes recurrentes** (desarrolladoras, inmobiliarias, empresas, instituciones): perfil con montos
  máximos mensuales que ellos estimen, usado **los primeros seis meses**; después se **evalúa al menos cada
  seis meses**, sobre clientes con acto de al menos seis meses de antigüedad.
- El sistema de alertas del Art. 23 Ter 2 compara cada acto con ese perfil.

### 4.7 Nivel de riesgo de la notaría (nivel A) y tipo de auditoría
```
Índice_notaría = 0.30·Mezcla_actos + 0.30·Mezcla_clientes + 0.20·Exposición_geográfica + 0.20·Canales_y_pagos
```
Cada componente se calcula con los 12 meses de datos (por ejemplo, % de actos de aviso siempre, % de clientes
ALTO, % de clientes PM nuevas, % de clientes extranjeros, % de intentos de pago en efectivo).
- **BAJO o MEDIO:** el dictamen puede emitirlo un área de auditoría interna o de control interno con
  personal independiente y capacitado (Art. 44).
- **ALTO**, o si el notario lo prefiere: **auditor externo independiente** con título, cédula y 3 años de
  experiencia, **certificación vigente de la UIF** (Art. 34 Bis), sin antecedentes por delitos patrimoniales,
  sin servicios previos que generen conflicto y sin haber sido Representante Encargado del auditado (Art. 45).

> **[INTERNO] Ojo con el Art. 44 en una notaría pequeña:** exige personal independiente del Representante
> Encargado. Un notario con dos colaboradores difícilmente lo cumple; el camino práctico es auditor externo
> certificado. Es un servicio a **referir**, no a prestar, por el conflicto de interés del Art. 45 d).

### 4.8 Calibración (para que no sea una caja negra)
- Con pocos actos al año, **no se pretende validación estadística**. Se documenta que los pesos son juicio
  experto y se revisan cada año con los datos reales.
- **Prueba de sensibilidad:** al calibrar, un caso que el notario considere ALTO debe salir ALTO; si no, se
  ajustan pesos y se anota el cambio en el acta de revisión.
- **Casos de control mínimos** que el sistema debe clasificar bien (ver documento 3, pruebas).

---

## 5. MODELO DE SERVICIO

| Fase | Duración sugerida | Qué se hace |
|---|---|---|
| 0. Diagnóstico | 1 a 2 semanas | Actos que realiza la notaría, volumen de 12 meses, qué actos **no** realiza (Art. 37 Bis 2: queda exenta de políticas para esos actos si lo hace constar en el Manual) |
| 1. Metodología | 2 semanas | Parametrización de pesos, mitigantes y reglas; acta de aprobación del notario |
| 2. Manual | 2 semanas | Personalización de la plantilla y formatos |
| 3. Mecanismos | 3 a 4 semanas | Alta del sistema, carga de históricos, integración con el conector de verificación elegido |
| 4. Capacitación | 1 sesión inicial + anual | Quien capacite debe acreditar **al menos cinco años de experiencia** en prevención de lavado (Art. 39 Bis fr. III) |
| 5. Operación | Continuo | Alertas, reevaluaciones semestrales, calendario de avisos (día 17), revisión anual |

**Estructura comercial (los montos los defines tú):**
- Implementación única (diagnóstico, metodología, manual, parametrización).
- Suscripción anual por notaría, escalonada por volumen de actos.
- Consumo de evidencia de verificación (conector) por consulta o por reconsulta, facturado aparte.
- Capacitación anual.
- **Paquete Colegio de Notarios:** licencia para integrantes, capacitación colectiva y reportes agregados.

> **[INTERNO] Preguntas de rentabilidad que debes responder antes de fijar precio:** costo por consulta de las
> fuentes de evidencia; horas de implementación por notaría; si el soporte lo da una persona con experiencia
> demostrable de cinco años (Art. 39 Bis fr. III) o un socio.

---

## 6. RIESGOS DEL PROYECTO Y CÓMO SE MANEJAN

| Riesgo | Manejo |
|---|---|
| **Conflicto de interés con la auditoría** (Art. 45 d) | No ofrecer auditoría. Tener una red de auditores externos certificados por la UIF y referirlos |
| **Confidencialidad penalmente tipificada** (Ley Art. 63 fr. II: 4 a 10 años por revelar información que vincule a una persona con un aviso o requerimiento, exista o no el aviso) | Los acuses y avisos se almacenan cifrados con llave del notario; acceso mínimo, bitácora de accesos, contrato de confidencialidad con el personal del proveedor; no se mezclan datos entre notarías |
| **Protección de datos** (LFPDPPP, supletoria por Ley Art. 4 fr. V) | el proveedor actúa como **encargado** del tratamiento de datos por cuenta del notario: contrato de encargo, aviso de privacidad del notario, consentimiento para reconsulta, retención limitada |
| **Secreto notarial** | La Ley lo resuelve para los avisos: presentarlos no transgrede el secreto ni puede ser objeto de cláusula de confidencialidad (Art. 22). Para otros usos de la información, que lo revise un abogado |
| **Normativa estatal del notariado** | Revisar caso por caso con asesor local; el producto no sustituye esa revisión |
| **Anexos 3 a 8 sin verificar** | Obtener el texto completo del Acuerdo antes de congelar el expediente de identificación |
| **Ejercicio de la abogacía sin licencia** | Los documentos se entregan como plantillas y soporte técnico; la revisión jurídica la hace el notario o un despacho aliado |
| **Cambio normativo (formatos de avisos, guías UIF)** | Parametrizar umbrales, fechas y listas; suscribirse al DOF y al Portal de la UIF |

---

## 7. PLAN DE LANZAMIENTO (propuesta)

1. **Semana 1 a 2:** completar verificaciones pendientes (Anexos 3 a 8, lineamientos de la UIF, ley estatal del
   primer mercado), y que un abogado PLD con experiencia notarial revise manual y metodología.
2. **Semana 3 a 8:** piloto con **2 o 3 notarías** (de preferencia con volumen mediano y un cliente recurrente).
   Meta: tener el Manual y la metodología documentados antes del **1-mar-2027**.
3. **Marzo 2027:** presentar el caso a un **Colegio de Notarios** como paquete colectivo.
4. **Junio 2027:** mecanismos automatizados operando en todas las notarías cliente.
5. **Indicadores del piloto:** horas por expediente, % de actos con grado calculado, alertas útiles vs. falsas,
   avisos presentados en plazo, satisfacción del notario.

---

## 8. PENDIENTES CONCRETOS

1. Obtener los **Anexos 3 a 8** del Acuerdo (DOF 7-ago-2026) y cerrar el expediente de identificación.
2. Confirmar la fecha de la **resolución de formatos de avisos** (de ella depende el aviso de 24 horas) y si el
   **Anexo 12-A** (avisos de protocolizaciones) ya está actualizado, por el transitorio Sexto fr. III del
   Reglamento.
3. Leer la **Evaluación Nacional de Riesgos** y las guías de la UIF cuando existan y ajustar la metodología.
4. Elegir el **primer estado** y estudiar su ley del notariado.
5. Definir **auditores externos certificados** con quienes trabajar como referencia.
6. Validar con abogado: que el proveedor actúa como encargado de datos, la lectura del Art. 20 para personas físicas y el manejo de listas.
