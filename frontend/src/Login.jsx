import axios from 'axios';
import React, { useState } from "react";

const Login = ({ onLoginSuccess }) => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [errorMensaje, setErrorMensaje] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrorMensaje(""); // Limpiamos errores previos

    try {
      // Axios hace la petición POST a tu Laravel
      const respuesta = await axios.post("http://localhost:8000/api/login", {
        email: email,
        password: password
      });

      // Si fue exitoso, guardamos el token y cambiamos de pantalla
      console.log("Datos recibidos:", respuesta.data);
      localStorage.setItem("token", respuesta.data.access_token);
      onLoginSuccess(); 

    } catch (error) {
      // Si Laravel responde con error (ej. contraseña incorrecta)
      setErrorMensaje("Correo o contraseña incorrectos");
    }
  };

  return (
    <div style={{ maxWidth: "300px", margin: "50px auto", fontFamily: "sans-serif" }}>
      <h2>Iniciar Sesión</h2> 
      
      {/* Mostramos mensaje de error si existe */}
      {errorMensaje && <p style={{ color: "red", fontWeight: "bold" }}>{errorMensaje}</p>}

      <form onSubmit={handleSubmit}> 
        <div style={{ marginBottom: "10px" }}>
          <label htmlFor="email">Correo electrónico:</label>
          <input
            type="email"
            id="email"
            placeholder="ejemplo@correo.com"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            style={{ width: "100%", padding: "8px" }}
          />
        </div>
        <div style={{ marginBottom: "10px" }}>
          <label htmlFor="password">Contraseña:</label>
          <input
            type="password"
            id="password"
            placeholder="******"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            style={{ width: "100%", padding: "8px" }}
          />
        </div>
        <button type="submit" style={{ width: "100%", padding: "10px", background: "#4CAF50", color: "white", border: "none", cursor: "pointer" }}>
          Entrar
        </button>
      </form>
    </div>
  );
};

export default Login;