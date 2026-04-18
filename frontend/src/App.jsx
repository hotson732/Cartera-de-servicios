import { useState } from 'react';
import Login from './Login';
import Mascotas from './Mascotas';

function App() {
  // Estado para saber si el usuario ya entró
  const [isLoggedIn, setIsLoggedIn] = useState(false);

  // Función para cerrar sesión
  const handleLogout = () => {
    localStorage.removeItem("token");
    setIsLoggedIn(false);
  };

  return (
    <div>
      {/* Si isLoggedIn es true, muestra Mascotas, si es false, muestra Login */}
      {isLoggedIn ? (
        <Mascotas onLogout={handleLogout} />
      ) : (
        <Login onLoginSuccess={() => setIsLoggedIn(true)} />
      )}
    </div>
  );
}

export default App;