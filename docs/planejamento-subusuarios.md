# Planejamento: subusuários por conta

## Como o sistema funciona hoje

- Cada login é um registro em `users`; `user_type` diferencia apenas administrador (`1`) de cliente (`2`). Não há conceito de titular de conta e subusuário.
- `client_user` é uma relação muitos-para-muitos entre usuários e clientes. O painel atual filtra a carteira pelo usuário autenticado.
- Empréstimos são `charges` de um cliente e têm `installments`. Um pagamento é identificado por `payment_date`, e a baixa também atualiza `amount_paid`, os totais da cobrança e o livro financeiro.
- O vencimento é editado diretamente na parcela. A criação de cliente, empréstimo, pagamento e vencimento são fluxos separados que poderão ser concedidos aos subusuários.
- O relatório e o saldo em `financial_transactions` são globais, sem vínculo a usuário ou conta. Os controllers de empréstimo, parcela e relatório também não verificam em todas as operações a carteira do usuário. Criar somente uma tela de permissões sem corrigir essas consultas não protege os dados.
- A exclusão de cliente é uma ação separada e não deve ser concedida implicitamente pelo direito de cadastrar. Investimentos, relatório financeiro e gerenciamento de usuários também não estão entre as permissões pedidas.

## Proposta de funcionamento

1. Um titular autenticado pode criar subusuários com nome, e-mail único e senha temporária ou fluxo de redefinição. Subusuários não podem criar outros usuários nem mudar seu papel.
2. Cada subusuário pertence a exatamente um titular. A autorização resolve o titular da sessão e limita clientes, cobranças, parcelas e telas à carteira desse titular; os subusuários do mesmo titular compartilham essa carteira, salvo decisão diferente.
3. O titular concede as permissões solicitadas: cadastrar cliente, criar empréstimo, registrar pagamento e alterar vencimento. Uma permissão negada deve bloquear a rota no servidor, mesmo com URL ou ID digitado manualmente.
4. O registro de cliente por subusuário associa o cliente ao titular da conta. Antes de criar empréstimo, pagar ou alterar uma parcela, o sistema verifica a propriedade do cliente por essa conta. Uma permissão de ação nunca autoriza acesso à carteira de outro titular.
5. O relatório só pode exibir dados do titular atual. O saldo financeiro deve ser segregado por conta ou derivado de lançamentos atribuídos à conta. Investimento permanece uma ação exclusiva do titular, a menos que o produto decida concedê-la explicitamente.
6. Desativar um subusuário revoga seu acesso sem apagar clientes, empréstimos ou pagamentos. A exclusão física da conta e a transferência de clientes precisam ser ações explícitas do titular.
7. Registros financeiros guardam quem fez a alteração (`created_by_user_id`) e a conta proprietária. Pagamento continua idempotente: a mesma parcela não pode ser baixada duas vezes. Mudanças de vencimento devem guardar data anterior, nova data, autor e horário para auditoria.

## Permissões propostas

| Ação | Titular | Subusuário |
|---|---|---|
| Ver painel e carteira da conta | Sim | Sim, somente com acesso à conta |
| Cadastrar cliente | Sim | Se concedida |
| Editar ou excluir cliente | Sim, conforme regras atuais | Não concedida por padrão |
| Criar empréstimo | Sim | Se concedida |
| Registrar pagamento | Sim | Se concedida |
| Alterar vencimento | Sim | Se concedida |
| Ver relatório e saldo financeiro | Sim, escopo da conta | Não por padrão |
| Registrar investimento | Sim | Não por padrão |
| Criar, desativar e configurar usuários | Sim, somente seus subusuários | Não |

## Migração e entrega sugeridas

1. Definir o modelo de titularidade, permissões iniciais e regra de compartilhamento de carteira; levantar usuários existentes e registrar explicitamente cada conta titular antes do backfill.
2. Adicionar relação de subordinação e estado ativo/inativo do usuário, permissões nomeadas e atribuição da conta nas tabelas financeiras; migrar os dados atuais sem trocar IDs nem expor carteiras entre titulares.
3. Implementar verificações reutilizáveis de permissão e escopo e aplicá-las a todos os controllers, downloads, relatórios, bindings e rotas de escrita.
4. Implementar tela do titular para listar, criar, editar permissões e desativar subusuários; incluir trilha de auditoria das ações financeiras e administrativas.
5. Validar isolamento entre duas contas, acesso permitido e negado para cada permissão, pagamento duplicado, desativação, cadastro feito por subusuário, relatório e saldo segregados, e migração dos dados existentes.
6. Publicar primeiro em staging com backup e plano de retorno. Fazer a migração de produção somente depois de verificar o mapeamento de titular para a carteira atual.

## Decisões ainda necessárias

- Um subusuário compartilha todos os clientes do titular ou pode receber uma carteira limitada?
- O titular pode editar, excluir e transferir clientes ou essas ações também devem aparecer como permissões configuráveis?
- Subusuários podem consultar a parcela e o saldo da conta ou somente executar as quatro operações autorizadas?
- Pagamentos e cobranças de todos os subusuários compõem o mesmo saldo financeiro do titular? Quem pode ver o relatório consolidado?
- O titular define cada permissão no convite ou todos começam com as quatro permissões ligadas?
- O primeiro acesso usa senha provisória, link de redefinição ou convite por e-mail? O serviço de envio de e-mail está configurado?
- O que fazer com clientes e movimentos antigos sem um titular claramente definido? O backfill deve ser revisado pelo proprietário antes de liberar a nova hierarquia.
