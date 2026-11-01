# Plano de implementação: titulares e subusuários

## Decisões confirmadas

- Um titular e todos os seus subusuários compartilham a mesma carteira completa de clientes.
- Só o titular edita, exclui ou transfere clientes. Essas ações não são permissões configuráveis para subusuários.
- Subusuários podem consultar clientes, empréstimos e parcelas da carteira, além das operações que tiverem habilitadas.
- O titular vê o relatório e o saldo consolidados da própria conta. Subusuários não veem o relatório nem o saldo consolidado.
- Os quatro acessos operacionais começam habilitados para cada subusuário. O titular pode alterar cada um individualmente:
  1. cadastrar clientes;
  2. criar empréstimos;
  3. registrar pagamentos;
  4. alterar vencimentos.
- O primeiro acesso será feito com senha provisória, sem e-mail. O subusuário deve ser obrigado a definir uma senha pessoal antes de usar o restante do sistema.
- Registros antigos sem titular identificável foram propostos para a conta de teste de Adolfo. Registros cuja conta possa ser inferida por vínculo existente devem preservar essa relação. O inventário somente leitura mais recente encontrou sete clientes: seis associados a Everton e um a Adolfo. O backfill precisa exibir o mapeamento final para revisão do proprietário antes de liberar a hierarquia.

## Leitura do sistema atual

- `users.user_type` hoje contém apenas os valores `1: Admin` e `2: Cliente`; não representa titular versus subusuário. A nova hierarquia não deve reutilizar esse campo como regra de propriedade.
- `client_user` é uma relação muitos-para-muitos usuário/cliente. O painel usa essa relação para obter a carteira do login atual. No inventário somente leitura atual há 2 usuários, 7 clientes, 7 associações e nenhum cliente sem vínculo: 6 clientes estão associados a Everton (usuário ID 2) e 1 a Adolfo (usuário ID 1).
- `charges` pertencem a clientes; `installments` pertencem a cobranças. Portanto, o escopo de acesso a parcela deve ser resolvido pela cobrança e pelo cliente até chegar à conta titular.
- `financial_transactions` não tem conta proprietária nem autor. No inventário somente leitura atual existem 10 movimentos: 6 deriváveis da carteira de Everton, 2 deriváveis da carteira de Adolfo e 2 investimentos sem vínculo, propostos para Adolfo. Não foram encontrados vínculos financeiros apontando para carteiras diferentes. Com a fórmula de saldo histórica do sistema (investimento soma; cobrança e pagamento subtraem), a projeção é R$ 4.982,00 para Adolfo e -R$ 9.290,00 para Everton, total -R$ 4.308,00. Esses valores são uma prévia técnica; o mapeamento continua sujeito à revisão do proprietário.
- O saldo atual é uma sequência global: `FinancialTransactionService` calcula cada novo saldo com base no último lançamento global. Para várias contas, isso mistura valores entre titulares.
- Os controllers consultam registros por ID sem checar de forma consistente se o cliente pertence à carteira do login. As rotas de cobrança, parcela e relatório não têm proteção de autenticação aplicada em um grupo comum. A implementação deve fechar essas falhas antes de criar os novos logins.
- Baixar uma parcela atualiza `payment_date`, valor pago, total da cobrança e o livro financeiro. O fluxo já impede uma segunda baixa quando `payment_date` está definido, mas a validação e a gravação dos registros relacionados ainda precisam ser protegidas por transação e escopo de conta.
- Alterar vencimento hoje só troca a data. Não há histórico de quem mudou, valor anterior ou horário.
- Excluir cliente hoje bloqueia a exclusão quando existem cobranças, mas o método remove apenas os vínculos do pivô quando não há cobranças; deve ser revisto na implementação para evitar uma falsa confirmação de exclusão.

## Modelo de dados proposto

1. Adicionar em `users` uma referência opcional ao titular (`account_owner_id`): nulo para titular; preenchida com o ID do titular para subusuário. Cada subusuário pertence a um único titular.
2. Guardar estado ativo/inativo e exigir troca da senha provisória no primeiro acesso.
3. Criar permissões nomeadas por subusuário para as quatro operações confirmadas. Toda negação deve valer no servidor e nas interfaces; esconder botão não substitui autorização na rota.
4. Definir um identificador canônico de conta titular nos clientes e nos lançamentos financeiros. Manter `client_user` apenas se ainda for necessário por compatibilidade; não usá-lo como única fonte de titularidade depois da migração.
5. Registrar `created_by_user_id` nos clientes, cobranças, pagamentos e mudanças de vencimento para auditoria. Os dados financeiros precisam ainda de `account_owner_id` para consolidar corretamente.
6. Guardar histórico de mudança de vencimento com parcela, data anterior, data nova, autor e horário.

## Regras de autorização e interface

- Titular: acesso completo à própria carteira, edição/exclusão/transferência de clientes, investimentos, relatório consolidado e criação/edição/desativação dos próprios subusuários.
- Subusuário ativo: leitura da carteira completa do titular. Pode executar apenas as quatro ações habilitadas para sua conta. Não cria usuários, não administra permissões, não edita/exclui/transfere clientes, não registra investimento e não vê saldo ou relatório consolidado.
- Conta inativa, senha provisória ainda não trocada ou subusuário suspenso: bloqueia operações até regularização.
- Toda busca ou escrita por ID segue esta cadeia de autorização: usuário → titular da conta → cliente → cobrança → parcela. IDs informados na URL ou formulário não podem atravessar essa fronteira.
- Pagamento grava parcela, totais da cobrança, transação financeira e autor na mesma transação de banco de dados; repetição concorrente da requisição não pode duplicar a baixa.
- Clientes cadastrados por subusuário ficam na carteira do titular e visíveis aos demais usuários da mesma conta.
- A tela do titular permite criar usuário, alterar quatro permissões, redefinir acesso e desativar. A senha provisória é gerada/definida no servidor, exibida uma única vez ao titular e exige troca no primeiro login. Não registrar senha em log.

## Backfill e revisão antes da ativação

1. Gerar backup de banco e inventário somente de leitura: contas/usuários, associações `client_user`, clientes sem associação, cobranças, parcelas e transações com seus vínculos.
2. Manter as carteiras existentes: seis clientes associados a Everton e um a Adolfo. Não transferir esses clientes por causa da atribuição proposta dos dois investimentos sem vínculo para Adolfo.
3. Derivar titular dos lançamentos que apontam para uma cobrança ou parcela com cliente já associado a uma conta.
4. Listar separadamente clientes sem associação e movimentos sem titular derivável. Propor Adolfo como titular desses registros ambíguos, resolver a conta pelo ID/e-mail cadastrado (não pelo nome sozinho) e apresentar totais por destino.
5. Esperar revisão explícita do proprietário do mapeamento — incluindo Adolfo como conta de teste — antes de executar o backfill em produção ou permitir login dos novos subusuários.
6. Aplicar a migração de forma idempotente, guardar relatório e contagens antes/depois e validar que cada cliente e movimento pertence a exatamente uma conta.

## Etapas de entrega

1. **Inventário e autorização**: consolidar IDs reais de titulares e registros órfãos, preparar matriz de rotas/ações e corrigir autenticação e isolamento nos fluxos atuais.
2. **Esquema e compatibilidade**: adicionar titularidade, permissões, estado de usuário, autores e histórico de vencimentos sem alterar dados existentes; preparar rollback.
3. **Serviços de domínio**: centralizar resolução de titular e verificação de permissão; atualizar clientes, cobranças, parcelas, transações, saldo e relatório para escopo da conta.
4. **Gestão de subusuários**: construir telas e operações de criação, permissões, redefinição de senha provisória, troca obrigatória de senha e desativação.
5. **Validação em staging**: testar titular e subusuário, cada permissão habilitada/desabilitada, leitura da carteira compartilhada, isolamento entre duas contas, acesso a IDs alheios, concorrência de pagamento, saldo consolidado e senha provisória.
6. **Revisão do backfill**: mostrar ao proprietário o relatório por titular e aguardar aceite explícito.
7. **Produção**: backup, deploy, backfill revisado, migração, verificação de permissões e rotas, depois liberar criação dos subusuários. Preservar caminho de rollback para código e banco.

## Andamento inicial

- A implementação começou na branch `codex/subusuarios`, separada do `master` publicado.
- O código local já contém o esquema aditivo para titularidade/permissões, middleware de escopo, gestão de senha provisória, as quatro permissões, consultas e lançamentos financeiros por titular, histórico de vencimento e transferência de cliente com histórico.
- O comando `accounts:backfill-ownership` apresenta o mapeamento completo em modo de prévia e só aplica após confirmação explícita. Registros sem vínculo e investimentos históricos são direcionados ao ID de titular informado (Adolfo); associações existentes são preservadas e vínculos conflitantes impedem a aplicação.
- A migração e o backfill ainda não foram executados no servidor. A hierarquia não será liberada antes da prévia de produção e revisão do proprietário.
- O cálculo de saldo atual trata o investimento conforme a regra existente e mantém a fórmula antiga para os demais movimentos. Uma futura alteração da interpretação contábil do pagamento exige uma decisão separada para evitar mudar saldos históricos sem revisão.

## Critérios para considerar concluído

- Um subusuário não alcança clientes ou operações de outra conta nem por URL/ID direto.
- As quatro permissões podem ser revogadas independentemente e são aplicadas no servidor.
- Todos os subusuários veem os clientes e parcelas da conta, mas não editam, excluem ou transferem clientes.
- O titular vê o relatório e o saldo consolidados da própria conta; lançamentos de outra conta não alteram esse saldo.
- Clientes, pagamentos e cobranças feitos por subusuários permanecem na conta titular e registram o autor.
- A troca de vencimento deixa histórico auditável; pagamentos não duplicam com requisições repetidas ou concorrentes.
- O relatório de backfill foi revisto pelo proprietário e as contagens por conta conferem antes da liberação.
