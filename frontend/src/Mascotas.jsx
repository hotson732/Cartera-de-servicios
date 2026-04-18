import React from 'react';

const Mascotas = ({ onLogout }) => {
  return (
    <div style={{ textAlign: "center", padding: "50px", fontFamily: "sans-serif" }}>
      <h1> Bienvenido al Refugio de Mascotas </h1>
      <p>¡Iniciaste sesión con éxito desde PostgreSQL!</p>
      
      <div style={{ display: "flex", justifyContent: "center", gap: "20px", marginTop: "30px" }}>
        <div style={{ padding: "20px", border: "1px solid #ccc", borderRadius: "8px" }}>
           <h3>Firulais</h3>
           <p>Edad: 2 años</p>
        </div>
        <div style={{ padding: "20px", border: "1px solid #ccc", borderRadius: "8px" }}>
           <h3>Michi</h3>
           <p>Edad: 1 año</p>
        </div>
      </div>

      <button 
        onClick={onLogout} 
        style={{ marginTop: "30px", padding: "10px 20px", background: "#ff4d4f", color: "white", border: "none", borderRadius: "5px", cursor: "pointer" }}>
        Cerrar Sesión
      </button>
    </div>
  );
};

export default Mascotas;