# thermalprint

Implementa a impressão direta em impressoras termicas através de :

- conexão local USB (impressora conectada diretamente no servidor)
- conexão local serial (impressora conectada diretamente no servidor)
- conexão CUPS (qualquer impressora compartilhada no servidor)
- conexão Ethernet (qualquer dispositivo IP)
- conexão windows (qualquer impressora compartilhada no servidor)

Esses casos funcionam unicamente em rede local.

Para impressão a partir de sistemas em Cloud é requerido a operação com javascript e alguma inteface local,
e para isso usa-se o jZebra usando o método printbase64() que imprime para uma impressora do computador local 
uma string formatada em base 64. É necessário JRE instalado e atualizado.
Como a aplicação jZebra é OpenSource a mesma é auto assinada então o Java irá RECUSAR seu uso.
Para corrigir esse problema é necessário incluir no console de segurança do java o certificado distribuido
junto com a aplicação jZebra.

As impressoras contidas nesta biblioteca são :

- Bematech
  * MP 2500-TH
  * MP 4200-TH
  * Nota: é necessário que o firmware esteja atualizado para a impressão do QR Code 
- Daruma
  * DR 700 L
  * DR 700 E
- Diebold
  * IM 402
  * IM 433
  * TSP 143
- Elgin
  * Vox
  * L42
   i9
- Epson
  * TM T20
  * TM T81
  * TM T88
- Star
  * TSP 100
  * TSP 700
- Toshiba
  * 1NR SureMark
  * 2CR SureMark
- Sweda
  * SI 300S
  * SI 300L

