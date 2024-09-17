//  Aqui você pode colocar todo o javaScript das páginas dentro de 'conteudos' 
// js/root.js
const modules = import.meta.glob('./dashboard/*.js');

for (const path in modules) {
  modules[path]().then(module => {
    console.log(`Loaded module from ${path}`);
    // Aqui você pode usar o módulo importado
  });
}
