import { startTransition, useEffect, useState } from 'react'
import './App.css'

const loginInitialState = {
  email: '',
  password: '',
}

const registerInitialState = {
  nombre: '',
  apellidos: '',
  email: '',
  role_id: '',
  dependencia_id: '',
  telefono: '',
  password: '',
  password_confirmation: '',
}

const dependencyRoleSlug = 'dependencia'

const idleRequestState = {
  loading: false,
  error: '',
  success: '',
}

async function apiRequest(path, options = {}) {
  const headers = {
    Accept: 'application/json',
    ...options.headers,
  }

  if (options.body) {
    headers['Content-Type'] = 'application/json'
  }

  const response = await fetch(path, {
    credentials: 'same-origin',
    ...options,
    headers,
  })

  const contentType = response.headers.get('content-type') ?? ''
  const data = contentType.includes('application/json')
    ? await response.json()
    : null

  if (!response.ok) {
    const error = new Error(data?.message ?? 'Request failed')
    error.status = response.status
    error.data = data
    throw error
  }

  return data
}

function getErrorMessage(error, fallbackMessage) {
  if (error?.data?.errors) {
    const firstMessage = Object.values(error.data.errors).flat()[0]

    if (firstMessage) {
      return firstMessage
    }
  }

  return error?.data?.message ?? fallbackMessage
}

function BrandMark() {
  return (
    <div className="brand-mark" aria-hidden="true">
      <span>GD</span>
    </div>
  )
}

function LockBadge() {
  return (
    <div className="lock-badge" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
        <path d="M7.5 10V7.75a4.5 4.5 0 1 1 9 0V10" />
        <rect x="5" y="10" width="14" height="10" rx="2.5" />
      </svg>
    </div>
  )
}

function MailIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
      <rect x="3.75" y="5.25" width="16.5" height="13.5" rx="2.25" />
      <path d="m5.25 7.5 6.75 5.25L18.75 7.5" />
    </svg>
  )
}

function PasswordIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
      <path d="M7.5 10V7.75a4.5 4.5 0 1 1 9 0V10" />
      <rect x="5" y="10" width="14" height="10" rx="2.5" />
      <path d="M12 13.5v3" />
    </svg>
  )
}

function UserIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
      <path d="M12 12a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" />
      <path d="M5.75 19.5a6.25 6.25 0 0 1 12.5 0" />
    </svg>
  )
}

function BriefcaseIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
      <rect x="3.75" y="7.5" width="16.5" height="11.25" rx="2.25" />
      <path d="M9 7.5V6a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 6v1.5" />
      <path d="M3.75 12.75h16.5" />
    </svg>
  )
}

function PhoneIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
      <path d="M8.25 4.5h2.25l1.5 3.75-1.5 1.5a13.41 13.41 0 0 0 3.75 3.75l1.5-1.5 3.75 1.5V15.75A2.25 2.25 0 0 1 17.25 18 12.75 12.75 0 0 1 6 6.75 2.25 2.25 0 0 1 8.25 4.5Z" />
    </svg>
  )
}

function App() {
  const [mode, setMode] = useState('login')
  const [loginForm, setLoginForm] = useState(loginInitialState)
  const [registerForm, setRegisterForm] = useState(registerInitialState)
  const [loginState, setLoginState] = useState(idleRequestState)
  const [registerState, setRegisterState] = useState(idleRequestState)
  const [dependencias, setDependencias] = useState([])
  const [roles, setRoles] = useState([])
  const [session, setSession] = useState({
    status: 'loading',
    user: null,
  })
  const [showLoginPassword, setShowLoginPassword] = useState(false)
  const [showRegisterPassword, setShowRegisterPassword] = useState(false)
  const [showRegisterConfirmation, setShowRegisterConfirmation] = useState(false)

  useEffect(() => {
    let ignore = false

    async function hydrateAuthScreen() {
      const [meResult, dependenciasResult] = await Promise.allSettled([
        apiRequest('/api/me'),
        apiRequest('/api/dependencias'),
      ])

      if (ignore) {
        return
      }

      if (dependenciasResult.status === 'fulfilled') {
        setDependencias(dependenciasResult.value.dependencias ?? [])
        setRoles(dependenciasResult.value.roles ?? [])
      }

      if (meResult.status === 'fulfilled' && meResult.value.user) {
        setSession({
          status: 'authenticated',
          user: meResult.value.user,
        })
        return
      }

      setSession({
        status: 'guest',
        user: null,
      })
    }

    hydrateAuthScreen()

    return () => {
      ignore = true
    }
  }, [])

  function updateLoginForm(event) {
    const { name, value } = event.target

    setLoginForm((current) => ({
      ...current,
      [name]: value,
    }))
  }

  function updateRegisterForm(event) {
    const { name, value } = event.target

    setRegisterForm((current) => ({
      ...current,
      [name]: value,
    }))
  }

  function changeMode(nextMode) {
    startTransition(() => {
      setMode(nextMode)
      setLoginState(idleRequestState)
      setRegisterState(idleRequestState)
    })
  }

  async function handleLoginSubmit(event) {
    event.preventDefault()

    setLoginState({ loading: true, error: '', success: '' })

    try {
      const data = await apiRequest('/api/login', {
        method: 'POST',
        body: JSON.stringify(loginForm),
      })

      setSession({
        status: 'authenticated',
        user: data.user,
      })
      setLoginState({ loading: false, error: '', success: data.message })
      setLoginForm(loginInitialState)
    } catch (error) {
      setLoginState({
        loading: false,
        error: getErrorMessage(error, 'No fue posible iniciar sesion.'),
        success: '',
      })
    }
  }

  async function handleRegisterSubmit(event) {
    event.preventDefault()

    setRegisterState({ loading: true, error: '', success: '' })

    try {
      const payload = {
        ...registerForm,
        role_id: registerForm.role_id ? Number(registerForm.role_id) : null,
        dependencia_id: registerForm.dependencia_id ? Number(registerForm.dependencia_id) : null,
      }

      const data = await apiRequest('/api/register', {
        method: 'POST',
        body: JSON.stringify(payload),
      })

      setSession({
        status: 'authenticated',
        user: data.user,
      })
      setRegisterState({ loading: false, error: '', success: data.message })
      setRegisterForm(registerInitialState)
    } catch (error) {
      setRegisterState({
        loading: false,
        error: getErrorMessage(error, 'No fue posible completar el registro.'),
        success: '',
      })
    }
  }

  async function handleLogout() {
    setLoginState(idleRequestState)
    setRegisterState(idleRequestState)

    try {
      await apiRequest('/api/logout', { method: 'POST' })
    } finally {
      setSession({
        status: 'guest',
        user: null,
      })
      changeMode('login')
    }
  }

  const activeState = mode === 'login' ? loginState : registerState
  const isBusy = loginState.loading || registerState.loading || session.status === 'loading'
  const selectedRole = roles.find((role) => String(role.id) === registerForm.role_id)
  const needsDependencia = selectedRole?.slug === dependencyRoleSlug

  return (
    <div className="app-shell">
      <header className="topbar">
        <div className="brand-block">
          <BrandMark />
          <div>
            <p className="brand-title">Gobierno Digital</p>
            <p className="brand-subtitle">Michoacan</p>
          </div>
        </div>
      </header>

      <main className="auth-page">
        <section className="auth-hero">
          <LockBadge />
          <p className="eyebrow">Acceso seguro</p>
          <h1>{session.user ? 'Sesion activa' : 'Iniciar sesion'}</h1>
          <p className="hero-copy">
            Accede con tus credenciales institucionales para registrar, dar
            seguimiento y administrar solicitudes.
          </p>
        </section>

        <section className="auth-card">
          {session.user ? (
            <div className="session-panel">
              <span className="pill">Acceso verificado</span>
              <h2>
                {session.user.nombre} {session.user.apellidos}
              </h2>
              <p className="session-copy">
                Tu sesion esta lista. Ya puedes continuar con el portal de la
                cartera de servicios.
              </p>

              <div className="session-grid">
                <article>
                  <span>Correo</span>
                  <strong>{session.user.email}</strong>
                </article>
                <article>
                  <span>Rol</span>
                  <strong>{session.user.role ?? 'Dependencia'}</strong>
                </article>
                <article>
                  <span>Dependencia</span>
                  <strong>{session.user.dependencia ?? 'Sin asignar'}</strong>
                </article>
                <article>
                  <span>Cargo</span>
                  <strong>{session.user.cargo || 'No especificado'}</strong>
                </article>
              </div>

              <div className="session-actions">
                <button className="primary-button" type="button" onClick={handleLogout}>
                  Cerrar sesion
                </button>
              </div>
            </div>
          ) : (
            <>
              <div className="panel-switch" role="tablist" aria-label="Cambiar formulario">
                <button
                  className={mode === 'login' ? 'is-active' : ''}
                  type="button"
                  role="tab"
                  aria-selected={mode === 'login'}
                  onClick={() => changeMode('login')}
                >
                  Iniciar sesion
                </button>
                <button
                  className={mode === 'register' ? 'is-active' : ''}
                  type="button"
                  role="tab"
                  aria-selected={mode === 'register'}
                  onClick={() => changeMode('register')}
                >
                  Crear cuenta
                </button>
              </div>

              {activeState.error ? <p className="status error">{activeState.error}</p> : null}
              {activeState.success ? <p className="status success">{activeState.success}</p> : null}

              {mode === 'login' ? (
                <form className="form-stack" onSubmit={handleLoginSubmit}>
                  <label className="field">
                    <span>Correo</span>
                    <div className="input-shell">
                      <span className="input-icon" aria-hidden="true">
                        <MailIcon />
                      </span>
                      <input
                        name="email"
                        type="email"
                        placeholder="correo@institucion.gob.mx"
                        autoComplete="email"
                        value={loginForm.email}
                        onChange={updateLoginForm}
                        required
                      />
                    </div>
                  </label>

                  <label className="field">
                    <span>Contrasena</span>
                    <div className="input-shell with-button">
                      <span className="input-icon" aria-hidden="true">
                        <PasswordIcon />
                      </span>
                      <input
                        name="password"
                        type={showLoginPassword ? 'text' : 'password'}
                        placeholder="Ingresa tu contrasena"
                        autoComplete="current-password"
                        value={loginForm.password}
                        onChange={updateLoginForm}
                        required
                      />
                      <button
                        className="toggle-visibility"
                        type="button"
                        onClick={() => setShowLoginPassword((value) => !value)}
                      >
                        {showLoginPassword ? 'Ocultar' : 'Ver'}
                      </button>
                    </div>
                  </label>

                  <button className="primary-button" type="submit" disabled={isBusy}>
                    {loginState.loading ? 'Validando acceso...' : 'Iniciar sesion'}
                  </button>
                </form>
              ) : (
                <form className="form-stack" onSubmit={handleRegisterSubmit}>
                  <label className="field">
                    <span>Tipo de usuario</span>
                    <div className="input-shell select-shell">
                      <select
                        name="role_id"
                        value={registerForm.role_id}
                        onChange={updateRegisterForm}
                        required
                      >
                        <option value="">Selecciona el tipo de acceso</option>
                        {roles.map((role) => (
                          <option key={role.id} value={role.id}>
                            {role.nombre}
                          </option>
                        ))}
                      </select>
                    </div>
                  </label>

                  <div className="field-grid">
                    <label className="field">
                      <span>Nombre</span>
                      <div className="input-shell">
                        <span className="input-icon" aria-hidden="true">
                          <UserIcon />
                        </span>
                        <input
                          name="nombre"
                          type="text"
                          placeholder="Nombre"
                          autoComplete="given-name"
                          value={registerForm.nombre}
                          onChange={updateRegisterForm}
                          required
                        />
                      </div>
                    </label>

                    <label className="field">
                      <span>Apellidos</span>
                      <div className="input-shell">
                        <span className="input-icon" aria-hidden="true">
                          <UserIcon />
                        </span>
                        <input
                          name="apellidos"
                          type="text"
                          placeholder="Apellidos"
                          autoComplete="family-name"
                          value={registerForm.apellidos}
                          onChange={updateRegisterForm}
                          required
                        />
                      </div>
                    </label>
                  </div>

                  <label className="field">
                    <span>Correo</span>
                    <div className="input-shell">
                      <span className="input-icon" aria-hidden="true">
                        <MailIcon />
                      </span>
                      <input
                        name="email"
                        type="email"
                        placeholder="correo@institucion.gob.mx"
                        autoComplete="email"
                        value={registerForm.email}
                        onChange={updateRegisterForm}
                        required
                      />
                    </div>
                  </label>

                  <div className="field-grid">
                    <label className="field">
                      <span>Dependencia</span>
                      <div className="input-shell select-shell">
                        <span className="input-icon" aria-hidden="true">
                          <BriefcaseIcon />
                        </span>
                        <select
                          name="dependencia_id"
                          value={registerForm.dependencia_id}
                          onChange={updateRegisterForm}
                          disabled={!needsDependencia}
                          required={needsDependencia}
                        >
                          <option value="">
                            {needsDependencia
                              ? 'Selecciona una dependencia'
                              : 'No aplica para trabajador de Gobierno'}
                          </option>
                          {dependencias.map((dependencia) => (
                            <option key={dependencia.id} value={dependencia.id}>
                              {dependencia.siglas
                                ? `${dependencia.nombre} (${dependencia.siglas})`
                                : dependencia.nombre}
                            </option>
                          ))}
                        </select>
                      </div>
                    </label>

                    <div className="role-help-card">
                      <span>Uso del rol</span>
                      <p>
                        {selectedRole?.slug === 'trabajador_gd'
                          ? 'Trabajador GobDigital corresponde a personal interno y no requiere dependencia.'
                          : 'Dependencia es el usuario comun del portal y debe quedar ligado a una dependencia.'}
                      </p>
                    </div>
                  </div>

                  <label className="field">
                    <span>Telefono</span>
                    <div className="input-shell">
                      <span className="input-icon" aria-hidden="true">
                        <PhoneIcon />
                      </span>
                      <input
                        name="telefono"
                        type="tel"
                        placeholder="443 000 0000"
                        autoComplete="tel"
                        value={registerForm.telefono}
                        onChange={updateRegisterForm}
                      />
                    </div>
                  </label>

                  <div className="field-grid">
                    <label className="field">
                      <span>Contrasena</span>
                      <div className="input-shell with-button">
                        <span className="input-icon" aria-hidden="true">
                          <PasswordIcon />
                        </span>
                        <input
                          name="password"
                          type={showRegisterPassword ? 'text' : 'password'}
                          placeholder="Minimo 8 caracteres"
                          autoComplete="new-password"
                          value={registerForm.password}
                          onChange={updateRegisterForm}
                          required
                        />
                        <button
                          className="toggle-visibility"
                          type="button"
                          onClick={() => setShowRegisterPassword((value) => !value)}
                        >
                          {showRegisterPassword ? 'Ocultar' : 'Ver'}
                        </button>
                      </div>
                    </label>

                    <label className="field">
                      <span>Confirmar contrasena</span>
                      <div className="input-shell with-button">
                        <span className="input-icon" aria-hidden="true">
                          <PasswordIcon />
                        </span>
                        <input
                          name="password_confirmation"
                          type={showRegisterConfirmation ? 'text' : 'password'}
                          placeholder="Repite la contrasena"
                          autoComplete="new-password"
                          value={registerForm.password_confirmation}
                          onChange={updateRegisterForm}
                          required
                        />
                        <button
                          className="toggle-visibility"
                          type="button"
                          onClick={() => setShowRegisterConfirmation((value) => !value)}
                        >
                          {showRegisterConfirmation ? 'Ocultar' : 'Ver'}
                        </button>
                      </div>
                    </label>
                  </div>

                  {dependencias.length === 0 ? (
                    <p className="helper-copy">
                    </p>
                  ) : null}

                  <button className="primary-button" type="submit" disabled={isBusy}>
                    {registerState.loading ? 'Creando cuenta...' : 'Crear cuenta'}
                  </button>
                </form>
              )}

              <div className="divider">
                <span>o tambien</span>
              </div>

              <button
                className="ghost-button"
                type="button"
                onClick={() => changeMode(mode === 'login' ? 'register' : 'login')}
              >
                {mode === 'login'
                  ? 'Solicitar acceso por primera vez'
                  : 'Ya tengo una cuenta'}
              </button>
            </>
          )}
        </section>

      </main>

      <footer className="footer-bar">
        <p>© 2026 Gobierno de Michoacan. Todos los derechos reservados.</p>
        <nav>
          <a href="/">Aviso de Privacidad</a>
          <a href="/">Terminos de Uso</a>
          <a href="/">Accesibilidad</a>
        </nav>
      </footer>
    </div>
  )
}

export default App
