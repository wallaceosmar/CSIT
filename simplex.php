<?php

  $expressao = $_GET["expression"];
  $nres = $_GET["hnr"];
  $restricoes[0] = $_GET["restricao01"];
  $restricoes[1] = $_GET["restricao02"];
  $restricoes[2] = $_GET["restricao03"];
  $restricoes[3] = $_GET["restricao04"];
  $restricoes[4] = $_GET["restricao05"];
  $restricoes[5] = $_GET["restricao06"];
  $restricoes[6] = $_GET["restricao07"];
  $restricoes[7] = $_GET["restricao08"];
  $restricoes[8] = $_GET["restricao09"];
  $restricoes[9] = $_GET["restricao10"];
  $ni = -1;
  $c = 0;
  $j=0;
  $nvd=0;
  $ncont=0;
  
  echo "Método Simplex";
  echo "<br/><br/>Expressão--> ".$expressao;
  
  for($i=0;$i<strlen($expressao);$i++)//Extrair variáveis de decisão.
  {
    if(is_numeric($expressao[$i])==true)//É Número.
	{
	   if($c==0)
	   {
	     $ni = $i;
	     $c = 1;
	   }
	   $ncont++;
	}
     else//Não é número.É variável ou sinal.
	{	
	   if($c==1)//Tinha começado um número.
	   {
		 $variadec[$j][0] = (float)substr($expressao,(int)$ni,(int)$ncont);
		 $variadec[$j][1] = $expressao[$i];
		 $c = 0;
		 $j++;
		 $nvd++;
		 $ncont=0;
	   }
	   else
	     if(strcmp($expressao[$i],'+')!=0 && strcmp($expressao[$i],'-')!=0)//Não é sinal.Provável 1.
		 {
		   $variadec[$j][0] = (float)1;
		   $variadec[$j][1] = $expressao[$i];
		   $j++;
		   $nvd++;
		 }
	}
       
  }
  
  echo "<br/>Variáveis de decisão--> ";
  for($i=0;$i<$nvd;$i++)
    echo "{$variadec[$i][0]}{$variadec[$i][1]} | ";

  for($i=0;$i<$nres;$i++)//Zerando matriz restricoes_p
     for($j=0;$j<$nvd+1;$j++)
        $restricoes_p[$i][$j] = 0;
   
  echo "<br/>";  
  $ncont=0;  
  for($m=0;$m<$nres;$m++)//Extrair restrições.
  {
   echo "<br/> Restrição $m --> $restricoes[$m]";
   for($i=0;$i<strlen($restricoes[$m]);$i++)
   {
    if(is_numeric($restricoes[$m][$i])==true)//É Número.
	{
	   if($c==0)
	   {
	     $ni = $i;
	     $c = 1;
	   }
	   $ncont++;
	   if($i==strlen($restricoes[$m])-1)
	   {
	     $restricoes_p[$m][$nvd] = (float)substr($restricoes[$m],(int)$ni,(int)$ncont);
	     $c = 0;
	     $ncont=0;
	   }
	}
    else//Não é número.É variável ou sinal.
	{   
	   if($c==1)//Tinha começado um número.
	   {
	         for($u=0;$u<$nvd;$u++)
		      if(strcmp($variadec[$u][1],$restricoes[$m][$i])==0)
		         $j=$u;
		 $restricoes_p[$m][$j] = (float)substr($restricoes[$m],(int)$ni,(int)$ncont);
		 $c = 0;
		 $ncont=0;
	   }
	   else
	     if(strcmp($restricoes[$m][$i],'+')!=0 && strcmp($restricoes[$m][$i],'-')!=0)//Não é sinal.Provável 1.
		 {
		   if(strcmp($restricoes[$m][$i],'<')==0||strcmp($restricoes[$m][$i],'>')==0)
		   {
		      if(strcmp($restricoes[$m][$i+1],'=')==0)
		        $i++;
		   }
		   else
		   {
		    for($u=0;$u<$nvd;$u++)
		      if(strcmp($variadec[$u][1],$restricoes[$m][$i])==0)
		         $j=$u;
		    $restricoes_p[$m][$j] = (float)1;
		   }
		 }
	}
   }}
   
  echo "<br/><br/> Matriz dos valores das restrições";
  for($i=0;$i<$nres;$i++)
  {
    echo"<br/>";
    for($p=0;$p<=$nvd;$p++)
      print str_pad("{$restricoes_p[$i][$p]}", 10,".", STR_PAD_RIGHT) ;
  }
  
  echo"<br/>";
  //Preenchendo linha Z.
  $tableau[0][0] = 'Z';
  for($i=0;$i<$nvd;$i++)
   $tableau[0][$i+1] = $variadec[$i][0]*-1;
  for($i=0;$i<$nres+1;$i++)
   $tableau[0][$i+1+$nvd] = 0;
  $tableau[0][1+$nvd+$nres] = 0;
   
  //Preenchendo linhas das restrições.
  for($j=1,$i=0;$j<=$nres;$j++)
  {
   $tableau[$j][0] = $j+$nvd;
   
   for($k=0;$k<$nvd;$k++)//Variáveis de decisão.
     $tableau[$j][$k+1] = $restricoes_p[$j-1][$k];
     
   for($k=0;$k<$nres;$k++)//Zerando variáveis de folga
     $tableau[$j][$k+1+$nvd] = 0;
     
   $tableau[$j][$j+$nvd] = 1;
   
   $tableau[$j][1+$nvd+$nres] = $restricoes_p[$j-1][$nvd];
  }
  
  echo "<br/><br/>Matriz Inicial Computada!";
  for($i=0;$i<$nres+1;$i++)
  {
    echo"<br/>";
    for($p=0;$p<$nvd+$nres+2;$p++)
    {
      if(is_float($tableau[$i][$p])==true)
         $print = number_format($tableau[$i][$p],1);
      else
         $print = $tableau[$i][$p];
      print str_pad("$print", 10,".", STR_PAD_RIGHT) ;
    }
  }
  
  $parada = 1;
  while($parada==1)//
  {
     //Achar o menor coeficiente da função objetivo.
     $menorc[0] = $tableau[0][1];
     $menorc[1] = 1;
     for($i=0;$i<$nvd;$i++)
       if($tableau[0][$i+1] < $menorc[0])
       {
          $menorc[0] = $tableau[0][$i+1];
          $menorc[1] = $i+1;
       }
     echo "<br/>Menor coeficiente --> $menorc[0]"."[".$menorc[1]."]";
       
     //Quem sai?
     $noelpo=0;
     for($k=0;$k<$nres;$k++)//Verificar se tem elemento positivo.
       if($tableau[$k+1][$menorc[1]] <= 0)
          $noelpo++;
     if($noelpo==$nres)//A solução deve parar.
     {
        $parada = 0;
        $noelpo = 0;
        break;
     }
     $noelpo = 0;
     
     for($k=0;$k<$nres;$k++)/*Dividir elementos da última coluna pelos correspondentes elementos positivos da coluna da variável a entrar na base.*/
       if($tableau[$k+1][$menorc[1]]>0)
           $quociente[$k] = ($tableau[$k+1][1+$nvd+$nres])/($tableau[$k+1][$menorc[1]]);
       else
           $quociente[$k] = -1;
       
     
     for($s=0;$s<=$nres;$s++)
        if($quociente[$s]>=0)
        {        
          $menorq[0] = $quociente[$s];//Procurar o menor quociente. Ele vai indicar a varável que sai.
          $menorq[1] = $s;
          break;
        }
     for($i=0;$i<$nres;$i++)
       if($quociente[$i] < $menorq[0] && $quociente[$i]>=0)
       {
          $menorq[0] = $quociente[$i];
          $menorq[1] = $i;//Variável que vai sair.
       }
     echo "<br/>Menor quociente --> $menorq[0]"."[".$menorq[1]."]";
     
     $pivo = $tableau[$menorq[1]+1][$menorc[1]];
     for($k=1;$k<=($nvd+$nres+1);$k++)//Dividindo linha($menorq[1]) pelo pivô.
       $tableau[$menorq[1]+1][$k] = ($tableau[$menorq[1]+1][$k])/$pivo;
       
     for($k=0;$k<$nres+1;$k++)/*Tornar a coluna [$menorc] em um vetor identidade com o elemento x na coluna($menorq[1]).*/
       if($tableau[$k][$menorc[1]]!=0 && $k!=$menorq[1]+1)//Deixar nulo todos os elementos da coluna.
       {
         $point = $tableau[$k][$menorc[1]]*-1;
         for($r=0;$r<($nvd+$nres+2);$r++)
           $tableau[$k][$r+1] = ($tableau[$menorq[1]+1][$r+1]*$point)+$tableau[$k][$r+1];
       }
       
     echo "<br/>"."<br/>";
     for($i=0;$i<$nres+1;$i++)
      {
        echo"<br/>";
        for($p=0;$p<$nvd+$nres+2;$p++)
        {
         if(is_float($tableau[$i][$p])==true)
           $print = number_format($tableau[$i][$p],1);
         else
           $print = $tableau[$i][$p];
         print str_pad("$print", 10,".", STR_PAD_RIGHT) ;
        }
      }
      
      $contp = 0;
      for($i=0;$i<($nvd);$i++)
       if($tableau[0][$i+1]<0)
         $contp++;
      if($contp==0)
        $parada = 0;
  }
?>