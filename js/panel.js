async function crearPost() {
  const title = document.getElementById("title").value;
  const content = document.getElementById("content").value;
  const category = document.getElementById("category").value;

  if (!title || !content) {
    alert("Completá título y contenido");
    return;
  }

  const { error } = await supabase.from("posts").insert([
    { title, content, category }
  ]);

  if (error) {
    alert("Error al publicar");
    console.error(error);
  } else {
    document.getElementById("title").value = "";
    document.getElementById("content").value = "";
    cargarPostsAdmin();
  }
}

async function cargarPostsAdmin() {
  const { data } = await supabase
    .from("posts")
    .select("*")
    .order("created_at", { ascending: false });

  const lista = document.getElementById("lista");
  lista.innerHTML = "";

  data.forEach(p => {
    lista.innerHTML += `
      <div class="admin-item">
        <strong>${p.title}</strong>
        <small>(${p.category})</small>
        <button onclick="borrarPost(${p.id})">🗑</button>
      </div>
    `;
  });
}

async function borrarPost(id) {
  if (!confirm("¿Eliminar publicación?")) return;

  await supabase.from("posts").delete().eq("id", id);
  cargarPostsAdmin();
}

async function logout() {
  await supabase.auth.signOut();
  window.location.href = "admin.html";
}

cargarPostsAdmin();
