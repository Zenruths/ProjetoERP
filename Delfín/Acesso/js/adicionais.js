// Botão Ocultar/Exibir Legendas de Status na tela de Contas a Receber
document.getElementById('toggleButton').addEventListener('click', function () {
    var legendContent = document.getElementById('legendContent');

    // Verifica se a legenda está visível ou não
    if (legendContent.style.display === "" || legendContent.style.display === "none") {
        legendContent.style.display = "block"; // Exibe a legenda
        this.textContent = "Ocultar Legenda"; // Muda o texto do botão
    } else {
        legendContent.style.display = "none"; // Esconde a legenda
        this.textContent = "Exibir Legenda"; // Muda o texto do botão
    }
});