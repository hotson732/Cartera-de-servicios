#!/bin/bash

REPO="hotson732/Cartera-de-servicios"

# =========================
# EP-1 (#10)
# =========================
BODY_EP1=$(cat <<EOF
## EP-1 · Autenticación

- [ ] #10.1 — Configurar Laravel Sanctum y rutas de autenticación
- [ ] #10.2 — Endpoint POST /api/register con validación y hash bcrypt
- [ ] #10.3 — Endpoint POST /api/login y revocación de token en logout
- [ ] #10.4 — Middleware CheckRole para los 4 roles
- [ ] #10.5 — Bitácora audit_logs: registrar login, logout y acciones sensibles
- [ ] #10.6 — Componente React de login con manejo de token en memoria
- [ ] #10.7 — Redirección por rol al iniciar sesión
EOF
)

gh issue edit 10 --repo $REPO --body "$BODY_EP1"

# =========================
# EP-2 (#11)
# =========================
BODY_EP2=$(cat <<EOF
## EP-2 · Catálogo

- [ ] #11.1 — Migracion y modelo CatalogoServicio con relaciones
- [ ] #11.2 — CRUD completo /api/catalog restringido a superadmin
- [ ] #11.3 — Subida y almacenamiento de imagen en storage/public/catalogo/
- [ ] #11.4 — Resource API con paginación y filtro por categoría
- [ ] #11.5 — Componente React de grid de cards con filtros de categoría
- [ ] #11.6 — Soft delete: verificar referencias históricas
EOF
)

gh issue edit 11 --repo $REPO --body "$BODY_EP2"

# =========================
# EP-3 (#12)
# =========================
BODY_EP3=$(cat <<EOF
## EP-3 · Gestión de solicitudes

- [ ] #12.1 — Generación de folio con secuencia PostgreSQL
- [ ] #12.2 — Endpoint POST /api/solicitudes
- [ ] #12.3 — Formulario dinámico React
- [ ] #12.4 — Subida de adjuntos polimórficos
- [ ] #12.5 — Panel de triage con filtros
- [ ] #12.6 — PATCH estatus + historial
- [ ] #12.7 — Hilo de aclaraciones
- [ ] #12.8 — Vista "Mis solicitudes"
EOF
)

gh issue edit 12 --repo $REPO --body "$BODY_EP3"

echo "✅ Texto agregado a issues padres"
