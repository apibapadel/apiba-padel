const SUPABASE_URL = "https://rwxoxjyjbeeriopfbnum.supabase.co";
const SUPABASE_KEY = "sb_publishable_cNoslpaObmOXACHkM5SYkw_1PYGmDlE";

// Solo crear la instancia si no existe
if (typeof supabase === "undefined") {
    var supabase = window.supabase.createClient(
        SUPABASE_URL,
        SUPABASE_KEY
    );
}
