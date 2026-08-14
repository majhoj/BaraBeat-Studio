<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="arrangement">
        <h2>9. Arranjo</h2><p>O Arranjo é a linha do tempo de sequências completas. Pattern da Biblioteca de Pattern são arrastados para seções e tocados em série ou paralelo.</p>
        <h3>Biblioteca de Pattern</h3><p>À esquerda estão todos os Pattern da partitura. Arraste-os para a linha do tempo ou use o botão mais para adicionar ao fim.</p>
        <h3>Seções</h3><p>Uma seção contém um ou mais Pattern. Os de uma linha tocam em paralelo. A repetição à esquerda define os passes. Acompanhamentos curtos podem continuar repetindo quando outro mais longo define a duração.</p>
        <h3>Adicionar em paralelo</h3><p>A zona paralela só aparece se a combinação for musicalmente válida. Um instrumento não pode tocar dois Pattern principais diferentes do mesmo tipo ao mesmo tempo, exceto quando um Solo substitui um Acompanhamento.</p>
        <h3>Células de Solo</h3><p>Seções repetidas de Acompanhamento mostram uma grade. Cada célula representa um passe; o Solo inserido só toca nesse passe.</p><ul><li>Se for menor, o restante fica em silêncio.</li><li>Se for maior, a seção continua.</li><li>Se o mesmo instrumento toca Acompanhamento e Solo, o Acompanhamento pausa durante o Solo e retorna depois.</li><li><strong>In</strong> e <strong>Out</strong> também valem para Solos.</li></ul>
        <h3>Mover seções</h3><p>As seções sobem ou descem na linha do tempo para reorganizar o Arranjo sem reconstruir Pattern.</p>
        <h3>BPM no Arranjo</h3><p>Cada seção pode ter BPM próprios. A mudança ocorre gradualmente por cerca de dois compassos e permanece até outra mudança.</p>
        <h3>Shekere e volumes</h3><p>O pulso de Shekere também está disponível. Perfil de Swing, Feel e volumes abrem acima da linha do tempo. Os volumes são compartilhados com o modo de prática e salvos com a partitura.</p>
        <h3>Desfazer e refazer</h3><p>As mudanças usam o mesmo histórico do editor e configurações. <kbd>Cmd</kbd> + <kbd>Z</kbd> desfaz; <kbd>Cmd</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd> refaz.</p>
      </section>
