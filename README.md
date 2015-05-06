# thermalprint

Implementa a impressão direta em impressoras termicas através de :

- conexão local USB (conectada no servidor)
- conexão local serial (conectada no servidor)
- conexão CUPS (qualquer compartilhamento definido no servidor)
- conexão Ethernet (qualquer dispositivo IP)
- conexão windows (qualquer impressora instalada ou compartilhada no servidor)

Esses casos funcionam unicamente em rede local.

Para impressão a partir de sistemas em Cloud é requerido a operação com javascript e alguma inteface local,
e para isso usa-se o jZebra usando o método printbase64() que imprime para uma impressora do computador local 
uma string formatada em base 64


