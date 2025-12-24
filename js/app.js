function toggleDark(){
 document.body.classList.toggle('dark');
 localStorage.setItem('dark',document.body.classList.contains('dark'));
}
if(localStorage.getItem('dark')==='true') document.body.classList.add('dark');
// js/app.js
async function cargarPosts() {
  // Traer los posts de Supabase
  const { data, error } = await supabase
    .from("posts")
    .select("*")
    .order("created_at", { ascending: false });

  if (error) {
    console.error("Error al cargar posts:", error);
    return;
  }

  // Seleccionar contenedor
  const contenedor = document.getElementById("posts");
  contenedor.innerHTML = "";

  // Iterar y mostrar cada post
  data.forEach(post => {
    contenedor.innerHTML += `
      <article class="post">
        <h3>${post.title}</h3>
        <p>${post.content}</p>
        <small>${new Date(post.created_at).toLocaleDateString()}</small>
      </article>
    `;
  });
}

// Llamar a la función al cargar la página
cargarPosts();
