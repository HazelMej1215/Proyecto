// ======================================================
// admin.js (versión correcta para PHP + sesiones)
// - Ya NO maneja login con localStorage
// - Ya NO redirige a login.html
// ======================================================


// ===== Helpers localStorage (solo para datos demo, por ahora) =====
const LS = {
  get(key, fallback = []) {
    try { return JSON.parse(localStorage.getItem(key) || JSON.stringify(fallback)); }
    catch { return fallback; }
  },
  set(key, value) { localStorage.setItem(key, JSON.stringify(value)); }
};


// =======================
// Registro de Películas
// =======================
const fileToBase64 = (file) => new Promise((resolve, reject) => {
  const reader = new FileReader();
  reader.onload = () => resolve(reader.result);
  reader.onerror = reject;
  reader.readAsDataURL(file);
});

const imagenInput = document.getElementById("imagen");
const imgPreview = document.getElementById("imgPreview");

imagenInput?.addEventListener("change", async () => {
  const file = imagenInput.files?.[0];
  if (!file) return;

  if (!file.type.startsWith("image/")) {
    alert("Selecciona un archivo de imagen válido.");
    imagenInput.value = "";
    return;
  }

  const base64 = await fileToBase64(file);
  if (imgPreview) {
    imgPreview.src = base64;
    imgPreview.style.display = "block";
  }
});

document.getElementById("formPelicula")?.addEventListener("submit", async (e) => {
  e.preventDefault();

  const nombre = document.getElementById("nombre")?.value.trim();
  const genero = document.getElementById("genero")?.value;
  const descripcion = document.getElementById("descripcion")?.value.trim();
  const trailer = document.getElementById("trailer")?.value.trim();
  const file = document.getElementById("imagen")?.files?.[0];
  const msg = document.getElementById("msg");

  if (!file) {
    alert("Debes cargar una imagen (archivo).");
    return;
  }

  try { new URL(trailer); } catch {
    alert("La URL del tráiler no es válida.");
    return;
  }

  const imagenBase64 = await fileToBase64(file);

  const pelicula = {
    id: crypto.randomUUID(),
    nombre,
    genero,
    descripcion,
    trailer,
    imagenBase64,
    activo: true,
    creadoEn: new Date().toISOString()
  };

  const lista = LS.get("peliculas", []);
  lista.push(pelicula);
  LS.set("peliculas", lista);

  if (msg) msg.textContent = "Película registrada correctamente (modo local).";
  e.target.reset();
  if (imgPreview) imgPreview.style.display = "none";
});


// =======================
// Consulta de Películas
// =======================
function renderPeliculas() {
  const tbody = document.getElementById("tbodyPeliculas");
  if (!tbody) return;

  const peliculas = LS.get("peliculas", []);
  tbody.innerHTML = "";

  if (peliculas.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5">No hay películas registradas.</td></tr>`;
    return;
  }

  for (const p of peliculas) {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><img class="thumb" src="${p.imagenBase64 || ""}" alt="img"></td>
      <td>${p.nombre}</td>
      <td>${p.genero}</td>
      <td>${p.descripcion}</td>
      <td>
        <div class="actions-col">
          <button class="btn-blue" data-act="activar" data-id="${p.id}">Activar</button>
          <button class="btn-red" data-act="inactivar" data-id="${p.id}">Inactivar</button>
          <button class="btn-yellow" data-act="modificar" data-id="${p.id}">Modificar</button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  }

  tbody.querySelectorAll("button").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      const act = btn.dataset.act;
      peliculasAccion(act, id);
    });
  });
}

function peliculasAccion(act, id) {
  const peliculas = LS.get("peliculas", []);
  const idx = peliculas.findIndex(x => x.id === id);
  if (idx === -1) return;

  if (act === "activar") peliculas[idx].activo = true;
  if (act === "inactivar") peliculas[idx].activo = false;

  if (act === "modificar") {
    // OJO: si tu archivo ya es .php, usa .php
    // Si todavía es .html, déjalo como .html
    const destino = document.querySelector('a[href*="peliculas_registro"]')?.getAttribute("href")
      || "peliculas_registro.php";
    location.href = `${destino}?edit=${encodeURIComponent(id)}`;
    return;
  }

  LS.set("peliculas", peliculas);
  renderPeliculas();
}


// =======================
// Registro/Consulta de Usuarios (localStorage)
// =======================
function renderUsuarios() {
  const tbody = document.getElementById("tbodyUsuarios");
  if (!tbody) return;

  const usuarios = LS.get("usuarios_admin", []);
  tbody.innerHTML = "";

  if (usuarios.length === 0) {
    tbody.innerHTML = `<tr><td colspan="4">No hay usuarios registrados.</td></tr>`;
    return;
  }

  for (const u of usuarios) {
    const tr = document.createElement("tr");
    const nombreCompleto = `${u.nombre} ${u.paterno} ${u.materno}`.trim();

    tr.innerHTML = `
      <td>${nombreCompleto}</td>
      <td>${u.correo}</td>
      <td>${u.fechaRegistro}</td>
      <td>
        <div class="actions-col" style="flex-direction:row;gap:10px">
          <button class="btn-red" data-act="eliminar" data-id="${u.id}">Eliminar</button>
          <button class="btn-blue" data-act="activar" data-id="${u.id}">${u.activo ? "Activo" : "Activar"}</button>
          <button class="btn-yellow" data-act="actualizar" data-id="${u.id}">Actualizar</button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  }

  tbody.querySelectorAll("button").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      const act = btn.dataset.act;
      usuariosAccion(act, id);
    });
  });
}

function usuariosAccion(act, id) {
  let usuarios = LS.get("usuarios_admin", []);
  const idx = usuarios.findIndex(x => x.id === id);
  if (idx === -1) return;

  if (act === "eliminar") {
    usuarios.splice(idx, 1);
    LS.set("usuarios_admin", usuarios);
    renderUsuarios();
    return;
  }

  if (act === "activar") {
    usuarios[idx].activo = !usuarios[idx].activo;
    LS.set("usuarios_admin", usuarios);
    renderUsuarios();
    return;
  }

  if (act === "actualizar") {
    const u = usuarios[idx];
    document.getElementById("usuarioId").value = u.id;
    document.getElementById("uNombre").value = u.nombre;
    document.getElementById("uPaterno").value = u.paterno;
    document.getElementById("uMaterno").value = u.materno;
    document.getElementById("uCorreo").value = u.correo;
    document.getElementById("uClave").value = u.clave;
    document.getElementById("msgUsuario").textContent = "Editando usuario...";
  }
}

document.getElementById("formUsuario")?.addEventListener("submit", (e) => {
  e.preventDefault();

  const id = document.getElementById("usuarioId").value || (crypto?.randomUUID?.() ?? String(Date.now()));
  const nombre = document.getElementById("uNombre").value.trim();
  const paterno = document.getElementById("uPaterno").value.trim();
  const materno = document.getElementById("uMaterno").value.trim();
  const correo = document.getElementById("uCorreo").value.trim().toLowerCase();
  const clave = document.getElementById("uClave").value.trim();

  let usuarios = LS.get("usuarios_admin", []);
  const idx = usuarios.findIndex(x => x.id === id);
  const fecha = new Date().toLocaleDateString("es-MX");

  const payload = {
    id, nombre, paterno, materno, correo, clave,
    activo: true,
    fechaRegistro: (idx === -1 ? fecha : usuarios[idx].fechaRegistro)
  };

  if (idx === -1) usuarios.push(payload);
  else usuarios[idx] = payload;

  LS.set("usuarios_admin", usuarios);

  document.getElementById("msgUsuario").textContent = "Guardado correctamente.";
  e.target.reset();
  document.getElementById("usuarioId").value = "";
  renderUsuarios();

  // Al guardar un nuevo usuario, genera una nueva clave automática (si existe)
  const inputClave = document.getElementById("uClave");
  if (inputClave && inputClave.hasAttribute("readonly")) {
    inputClave.value = generarClave(8);
  }
});


// =======================
// Clave automática (usuarios)
// =======================
function generarClave(longitud = 8) {
  const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
  let clave = "";
  for (let i = 0; i < longitud; i++) {
    clave += chars[Math.floor(Math.random() * chars.length)];
  }
  return clave;
}

document.addEventListener("DOMContentLoaded", () => {
  // Generación automática en usuarios_registro
  const inputClave = document.getElementById("uClave");
  const btnGen = document.getElementById("btnGenerarClave");

  if (inputClave) {
    // Si NO estás editando usuario, genera clave nueva
    const hiddenId = document.getElementById("usuarioId");
    if (!hiddenId?.value) inputClave.value = generarClave(8);
  }

  btnGen?.addEventListener("click", () => {
    if (inputClave) inputClave.value = generarClave(8);
  });

  // Render si la tabla existe en la página
  renderPeliculas();
  renderUsuarios();
});
