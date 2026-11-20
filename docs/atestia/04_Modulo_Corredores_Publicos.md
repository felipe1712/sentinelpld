# ATESTIA — MÓDULO CORREDORES PÚBLICOS (Apartado B)
### v1.1 · 19-nov-2026 · Complementa el módulo Notarías (docs 02, 03 y 05)

> **Alcance del producto:** solo **notarios (Apartado A)** y **corredores públicos (Apartado B)**. Servidores públicos con fe pública (C) y facilitadores MASC (D) quedan fuera.
> **Estado de verificación.** Leí Ley Art. 17 fr. XII B y Reglamento Art. 29. **No** he leído la Ley Federal de Correduría Pública ni los formatos oficiales de aviso para B.

---

## 1. Qué dice la norma
| Inciso | Actividad Vulnerable (Ley Art. 17 fr. XII B) | Notas verificadas |
|---|---|---|
| a) | **Avalúos** de bienes con valor ≥ 8,025 UMA | Reglamento Art. 29: aplica cuando en el avalúo se **utiliza la fe pública** |
| b) | Constitución y modificación patrimonial (capital, fusión, escisión) de **personas morales mercantiles** y compraventa de acciones y partes sociales | Aviso "en términos de los incisos"; la norma **no fija umbral** |
| c) | Constitución, modificación o **cesión de derechos** de fideicomisos (salvo garantía a favor de entidades financieras) | Igual |
| d) | **Mutuo o crédito mercantil** con acreedor fuera del sistema financiero | Igual |

- **Acumulación:** el Acuerdo Art. 19 la excluye para **todos los incisos del Apartado B** (verificado).
- El corredor puede ser **persona física o moral** (Representante Encargado si es persona moral, Ley Art. 20).
- Rigen igual: Art. 34 Bis (certificación UIF opcional), Arts. 22 y 38 (confidencialidad) y Art. 63 fr. II (sanción penal).

## 2. Diferencias frente al módulo Notarías
| Tema | Notarios | Corredores |
|---|---|---|
| Sujeto | Persona física | Persona física o moral |
| Catálogo | Inmuebles, poderes, sociedades, fideicomisos, mutuos | **Avalúos**, sociedades mercantiles, fideicomisos (incl. cesión), mutuos mercantiles |
| Acumulación 6 meses | Sí en a), c), d) | **No** |
| Aviso de inmuebles | Sistema fiscal del SAT | No aplica (no es inciso A-a) |
| Representante Encargado | No | Sí si es persona moral |

## 3. Diseño del módulo
| Inciso | Regla del motor |
|---|---|
| B-a | Umbral 8,025 UMA sobre el valor del bien; solo si se usa fe pública |
| B-b | Aviso por acto; umbral por confirmar |
| B-c | Aviso por acto; excluye garantías a entidades financieras; incluye cesión |
| B-d | Aviso por acto |
**Indicadores propios de riesgo** (metodología, doc 02 §4.3): valuación inflada o deprimida frente a referencias, avalúos repetidos del mismo bien, sociedades recientes que transmiten acciones en menos de {{param}} días, mutuos mercantiles circulares. Marcados [ORPI]; son diseño propio, no norma.

## 4. Pendientes para habilitar Corredores
1. Leer la Ley Federal de Correduría Pública y su reglamento (actos y fe pública en avalúos).
2. Confirmar con abogado PLD o la UIF: umbrales de B-b a B-d.
3. Obtener formatos oficiales de aviso para B y la resolución de formatos 2026.
4. Piloto con 1 o 2 corredurías.
