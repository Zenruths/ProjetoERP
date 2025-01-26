/* Botão Ocultar/Exibir Legendas de Status na tela de Contas a Receber */

document.getElementById('toggleButton').addEventListener('click', function () {
    var legendContent = document.getElementById('legendContent');
    var legendContainer = document.getElementById('legendContainer');

    // Verifica se a legenda está visível ou não
    if (legendContent.style.display === "" || legendContent.style.display === "none") {
        legendContent.style.display = "block"; // Exibe a legenda
        legendContainer.querySelector('ul').style.display = "block"; // Exibe a lista
        this.textContent = "Ocultar Legenda"; // Muda o texto do botão
    } else {
        legendContent.style.display = "none"; // Esconde a legenda
        legendContainer.querySelector('ul').style.display = "none"; // Esconde a lista
        this.textContent = "Exibir Legenda"; // Muda o texto do botão
    }
});