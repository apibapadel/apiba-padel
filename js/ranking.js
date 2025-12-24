async function cargarRanking() {
  const { data, error } = await supabase
    .from("ranking")
    .select("*")
    .order("points", { ascending: false });

  if (error) {
    console.error(error);
    return;
  }

  const tabla = document.getElementById("ranking");
  tabla.innerHTML = "";

  data.forEach((j, index) => {
    tabla.innerHTML += `
      <tr>
        <td>${index + 1}</td>
        <td>${j.player}</td>
        <td>${j.points}</td>
      </tr>
    `;
  });
}

cargarRanking();
