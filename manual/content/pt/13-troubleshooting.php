<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="troubleshooting">
        <h2>13. Solução de problemas</h2>
        <h3>Um Pattern não é reconhecido</h3><ul><li>Confira os seletores sobre o Pattern.</li><li>Confira se as notas estão na grade.</li><li>Em 9/8, coloque In, Out e ShortBar corretamente.</li></ul>
        <h3>A reprodução soa deslocada</h3><ul><li>Confira a latência Bluetooth.</li><li>Revise Feel.</li><li>Nas transições, revise In/Out e ShortBar.</li></ul>
        <h3>As notas em movimento mostram espaços vazios</h3><ul><li>Confira repetições muito longas ou muitos Pattern simultâneos.</li><li>No iPhone/Safari, use valores realistas.</li><li>Salve e reabra arquivos novos para atualizar metadados.</li><li>Em transições com Call, Intro, ShortBar ou Out do Acompanhamento, confira o cenário salvo.</li></ul>
        <h3>A linha do tempo mostra “misto”</h3><p>Pode ocorrer quando Pattern paralelos têm repetições diferentes. Grupos de Acompanhamento de um passe prolongados em uma seção não deveriam mais aparecer como “misto”.</p>
        <h3>Falta um sample</h3><p>O player deve mostrar o erro. Confira nome e presença no servidor.</p>
        <h3>O editor móvel não aparece</h3><p>Só é ativado em paisagem. Desative o bloqueio de orientação e gire novamente. O retrato mantém a leitura compacta.</p>
        <h3>Não há som no iPhone</h3><p>Confira volume, saída e Bluetooth. A sessão de reprodução normalmente permite áudio no silencioso; após mudanças de iOS ou navegador, pode ser necessário tocar Play novamente.</p>
        <h3>Dados offline faltam após reinstalar</h3><p>iOS pode ter criado outro armazenamento. Carregue novamente do servidor. Arquivos apenas locais exigem backup prévio.</p>
        <h3>Um arquivo do servidor não mostra a versão esperada</h3><p>Reabra o diálogo para atualizar a lista. Confira status e modificação. <strong>Carregar versão do servidor</strong> substitui a local após confirmação.</p>
        <h3>Uma publicação não pode ser atualizada ou excluída</h3><p>É necessário o token local. Use o navegador ou app que publicou. Um arquivo apenas carregado pode ser lido, mas não recebe permissão de gerenciamento.</p>
        <h3>O idioma não altera a partitura</h3><p>É intencional: apenas controles e mensagens são traduzidos. Nomes, textos, rótulos de Pattern e dados musicais permanecem.</p>
        <h3>SVG ou PDF não é encontrado</h3><p>A exportação é entregue como download. Confira a pasta ou, no iPhone, Arquivos e downloads do Safari. Restrições podem exigir confirmação.</p>
      </section>
