# Cadastros Automatizados

Este projeto foi desenvolvido especificamente *para um cliente* com o objetivo de resolver problemas de agilidade em interfaces durante o processo de cadastro. Através da automação de tarefas que anteriormente eram manuais, buscamos melhorar a experiência do usuário e otimizar a eficiência do processo. O código implementa funcionalidades automáticas no CMS, simplificando o fluxo de trabalho administrativo e proporcionando uma solução personalizada para as necessidades do cliente.

## Objetivo
O principal objetivo é simplificar o processo de cadastro, tornando-o mais intuitivo e rápido para os usuários, especialmente aqueles que não estão familiarizados com CMS. A abordagem adotada aproveita as funcionalidades existentes do CMS e plugins, focando em inserções no banco de dados para manter a compatibilidade com o sistema atual e respeitar as restrições de tempo e orçamento do cliente.

## Solução Implementada
- **Registro de Menu no Painel Administrativo:** Adiciona uma opção de menu para cadastrar novos processos seletivos.
- **Formulário de Cadastro:** Um formulário simples que contempla todas as informações necessárias para a criação de associações específicas ao projeto.
- **Inserção no Banco de Dados:** Os dados do formulário são processados e inseridos diretamente nas tabelas relevantes do banco de dados do WordPress.
- **Criação e Publicação de Páginas:** Automatiza a criação e publicação de páginas para cada processo seletivo, protegendo-as com senhas específicas.
- **Proteção de Páginas:** Utiliza CPFs fornecidos no cadastro para proteger páginas com múltiplas senhas.
- **Atualização da Descrição:** Insere links úteis na descrição dos níveis de associação criados para facilitar o acesso às informações necessárias.

## Plugins Utilizados
- **Paid Memberships Pro:** Gerenciamento de níveis de associação e grupos.
- **Password Protect WordPress (PPWP):** Proteção de páginas com senhas múltiplas.
- **Gutenberg Blocks:**  Utilizado para a criação de conteúdos visuais e estruturados nas páginas criadas automaticamente.

Este projeto é um produto mínimo viável (MVP) que visa entregar uma solução funcional dentro das limitações atuais do projeto, podendo servir como base para futuras automatizações e expansões.

## Antes e Depois: Simplificação do Processo de Cadastro

### Antes
Anteriormente, o processo envolvia acessar uma área de cadastro, navegar por várias sanfonas e seleções para selecionar o tipo de associação, criar páginas manualmente e depois voltar ao cadastro para ajustar opções de descrição.

### Hoje
Com a implementação deste projeto:
- **Acesso Direto:** Os usuários acessam diretamente um menu no painel administrativo para cadastrar novos processos seletivos.
- **Simplicidade no Cadastro:** Um formulário simples centraliza todas as opções necessárias para criar associações específicas ao projeto.
- **Automatização de Páginas:** As páginas são criadas automaticamente usando funcionalidades nativas do CMS WordPress e plugins utilizados.
- **Redução de Cliques:** Minimiza o número de cliques necessários para concluir o cadastro, melhorando a eficiência e a experiência do usuário.

### Importância da Minimização de Cliques
A redução do número de cliques é crucial para melhorar a usabilidade e a eficiência do sistema. Ao simplificar o processo de cadastro para um mínimo de cliques, facilitamos a navegação e a interação dos usuários, especialmente aqueles menos familiarizados com CMS. Isso não apenas aumenta a produtividade, mas também reduz a frustração ao realizar tarefas repetitivas, tornando o sistema mais acessível e intuitivo.
