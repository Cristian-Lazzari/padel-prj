<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Contratto di Consulenza</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 40px;
        }
        h1, h2, h3 {
            text-align: center;
        }
        .container {
            border: 1px solid #000;
            padding: 20px;
            margin-top: 20px;
        }
        .firma {
            margin-top: 50px;
            text-align: center;
        }
        .firma span {
            display: block;
            border-top: 1px solid #000;
            width: 200px;
            margin: 0 auto;
            padding-top: 5px;
            font-size: 10px;
            font-style: italic;
            
        }
        .sub{
            text-align: center;
            font-size: 16px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 20px;">
        <img style="box-shadow: 0 0 10px rgba(0, 0, 0, 0.4); background: #090333; padding:20px; border-radius:30px" src="{{ public_path('logo.png') }}" width="200" alt="Logo Future Plus">
    </div>
    <h1>CONTRATTO DI CONSULENZA</h1>
    <p class="sub"><strong>Tra</strong></p>
    <p>La Future Plus di Lazzari Cristian, con sede legale in Monte San Vito (AN), in via Vicolo Leonori n° 2,  C.F.  LZZCST02A26H211P,  P.IVA  02974730422  (di  seguito  “Consulente”),  indirizzo  e-mail futureplus.commerciale@gmail.com,  indirizzo  PEC  futureplus@pec.it,  sito  web  https://future-plus.it</p>
    <p class="sub">e</p>
    <p>L' attività <strong>{{$consumer['activity_name']}}</strong>, con sede in <strong>{{$consumer['address']}}</strong>, C.F. <strong>{{$consumer['owner_cf']}}</strong>, P.IVA <strong>{{$consumer['vat']}}</strong> (di seguito “Cliente”), indirizzo e-mail <strong>{{$consumer['activity_email']}}</strong>, indirizzo PEC <strong>{{$consumer['pec']}}</strong></p>
    
    <h2>PREMESSO CHE</h2>
    <p>A - il Consulente svolge attività di consulenza per la realizzazione di siti web, per la creazione e lo sviluppo delle funzionalità e delle implementazioni delle pagine web, con lo scopo di aiutare il Cliente ad accrescere la propria visibilità sul mercato e ad implementare i servizi digitali necessari a migliorare ed incrementare il proprio business nel mercato di riferimento;</p>
    <p>B - il Cliente intende usufruire dei servizi offerti dal Consulente, in proprio o da parti terze, che quest’ultimo mette a disposizione del Cliente, nei termini ed alle condizioni che seguono;</p>
    
    <p>tutto ciò premesso e facente parte integrante e sostanziale del presente contratto, le Parti,</p>
    <h2>CONVENGONO E STIPULANO QUANTO SEGUE</h2>
    <div style="page-break-before: always;"></div>
    <h3>Articolo 1</h3>
    <p class="sub">Oggetto della Prestazione</p>

    <p>La Future Plus di Lazzari Cristian viene incaricata dell’attività come di seguito descritta. </p>

    <p>Con la sottoscrizione del presente contratto il Cliente affida al Consulente, che accetta e si
        impegna ad eseguire nei confronti del Cliente, i Servizi di cui alle clausole seguenti, impegnandosi il
        Consulente ad organizzare, eseguire e curare la prestazione del Servizio e con organizzazione e
        mezzi propri adeguati, nonché a garantire i migliori standard tecnico-qualitativi nel rispetto delle
        modalità di esecuzione e coordinamento concordate tra le Parti in conformità.
    </p>

    <p>Il Consulente si impegna a prestare il Servizio con professionalità e con il massimo grado di
        diligenza e perizia richiesti anche tenuto conto della natura del Servizio stesso, in conformità a
        quanto previsto dal secondo comma dell'articolo 1176 c.c. e secondo i termini e le condizioni del
        presente Contratto.
    </p>

    <p>I Servizi offerti dal Consulente consisteranno nell’espletamento di attività di consulenza riguardante i seguenti aspetti:</p>
    <ul>
        <li> <strong> Presenza online: </strong> creazione e sviluppo di un sito web;</li>
        <li> <strong> Pannello di amministrazione: </strong> creazione di una dashboard con accesso privato in cui
            monitorare e gestire i dati e i servizi del sito web;
        </li>
        <li> <strong> Creazione di post: </strong> il Cliente può creare post nella sua Dashboard che saranno poi visibili nel
            sito web;
        </li>
        <li> <strong> Qr-code: </strong> creazione e personalizzazione di un qr-code;</li>
        <li> <strong> Prenotazione di servizi online: </strong> invio di prenotazioni di servizi da parte di soggetti terzi
            fruitori del sito web con gestione delle prenotazioni direttamente dal pannello di
            amministrazione;
        </li>
        <li> <strong> Ordinazione di prodotti online: </strong> ordinazione di prodotti da parte di soggetti terzi fruitori del
            sito web con gestione delle ordinazione direttamente dal pannello di amministrazione;
        </li>
        <li> <strong> Pagamenti online: </strong> viene data la possibilità di sfruttare i servizi di Stripe attraverso il sito
            fornito nel servizio;
        </li>
        <li> <strong> Statistiche accurate: </strong> possibilità di consultare le statistiche sui servizi utilizzati nel sito web
            pagina;
        </li>
        <li> <strong> Email Marketing: </strong> Il servizio include strumenti di email marketing per l’invio di comunicazioni
            automatizzate e promozionali ai clienti. È possibile creare campagne mirate, segmentare il
            pubblico in base alle preferenze e monitorare le performance delle email inviate;
        </li>
        <li> <strong> Notifiche WhatsApp: </strong> Il sistema invia ai ristoratori notifiche automatiche tramite WhatsApp
            per la gestione degli ordini e delle prenotazioni. Attraverso un messaggio interattivo, il
            ristoratore può confermare o annullare direttamente l’ordine o la prenotazione,
            semplificando il processo di gestione senza necessità di accedere alla piattaforma.
        </li>
    </ul>
    <p>
        La prestazione resa dal Consulente comprende, altresì, l’assistenza in ordine alla gestione della
        pagina web, della piattaforma digitale, del dominio e delle relative funzionalità (tra cui, a titolo
        esemplificativo, la configurazione di disponibilità e servizi).
    </p>
    <p>
        Tali attività saranno ricomprese, ai fini del pagamento del corrispettivo, nei pacchetti di
        abbonamento di cui al successivo Articolo 3.
    </p>
    <div style="page-break-before: always;"></div>
    <h3>Articolo 2</h3>
    <p class="sub">Durata</p>
    <p>
        La durata del presente Contratto è di 12 mesi a decorrere dalla data di sottoscrizione, rinnovabili
        tacitamente.
    </p>
    <p>
        L’eventuale disdetta dovrà avvenire entro e non oltre i due mesi antecedente la scadenza indicata
        al periodo precedente, mediante PEC e/o lettera raccomandata A/R da recapitarsi agli indirizzi
        indicati in epigrafe.
    </p>
    <p>
        Al termine del contratto, il Consulente, con l’aiuto più collaborativo possibile del Cliente, si obbliga
        a ripristinare la situazione e le funzionalità del sito web antecedenti all’inizio del presente incarico,
        rispettando, altresì, i diritti di proprietà e di proprietà intellettuale del relativo titolare.
    </p>
    <div style="page-break-before: always;"></div>
    <h3>Articolo 3</h3>
    <p class="sub">Corrispettivo</p>

    <p>
        A titolo di corrispettivo per la prestazione dei Servizi indicati all’articolo 1, il Committente verserà
        al Consulente la somma corrispondente al prezzo dell’abbonamento selezionato.
    </p>
    <p>
        I Pacchetti (abbonamenti) offerti dal Consulente sono i seguenti, per gli importi indicati:
    </p>

    <h4>
        Pacchetto Essentials Include:
    </h4>
    <ul>
        <li> Servizi Presenza Online; </li>
        <li> Pannello di Amministrazione; </li>
        <li> QR-Code. </li>
    </ul>
    <p>
        Importo mensile: € 49,00. 
        Importo annuale (in un’unica soluzione): € 399,00.
    </p>
    <h4>
        Pacchetto Work On Include:
    </h4>
    <ul>
        <li>Servizi Presenza Online;</li>
        <li>Creazione di Post;</li>
        <li>Pannello di Amministrazione;</li>
        <li>QR-Code;</li>
        <li>Ordinazione di Prodotti Online;</li>
        <li>Prenotazione di Servizi Online.</li>
    </ul>
    <p>
        Importo mensile: € 99,00. 
        Importo annuale (in un’unica soluzione): € 999,00.
    </p>
       
    <h4>
        Pacchetto Boost Up Include:
    </h4>
    <ul>
        <li>Servizi Presenza Online;</li>
        <li>Creazione di Post;</li>
        <li>Pannello di Amministrazione;</li>
        <li>QR-Code;</li>
        <li>Ordinazione di Prodotti Online;</li>
        <li>Prenotazione di Servizi Online;</li>
        <li>Statistiche Accurate;</li>
        <li>Pagamenti Online;</li>
        <li>Email Marketing;</li>
        <li>Notifiche WhatsApp.</li>
    </ul>
    <p>
        Importo mensile: € 129,00. 
        Importo annuale (in un’unica soluzione): € 1.199,00.
    </p>
    <p>
        Nel caso di prima iscrizione, verrà applicato un bonus per cui i primi 30 giorni vengono considerati
        come periodo di prova, ciò comporta che il cliente durante il suddetto periodo di prova potrà
        recedere dal contratto liberamente ricevendo un rimborso pari alla quota versata entro 14 giorni,
        nel caso di pagamento per mezzo di Stripe la somma verrà prelevata una volta terminato il periodo
        di prova, salvo recessione entro i 30 giorni.
    </p>
    <p>
        Il versamento delle predette somme dovrà avvenire a mezzo bonifico bancario, a mezzo
        pagamento integrato tramite il sito web del Consulente, o secondo le modalità concordate tra le
        Parti.
    </p>
    <div style="page-break-before: always;"></div>
    <h3>Articolo 4</h3>
    <p class="sub">Scelta dell’Abbonamento</p>
    <p>
        Il Cliente, con la sottoscrizione del presente contratto, dichiara di scegliere il seguente Pacchetto
        (apporre una X sull’abbonamento scelto):
    </p>
    
    <ul style="list-style: none;">
        <li> 
            @if ($consumer['status'] == 4)
                <img src="{{ public_path('sx.png') }}" width="15" style="margin:3px;" alt="Box conferma">
            @else
                <img src="{{ public_path('square.png') }}" width="15" style="margin:3px;" alt="Box conferma">
            
            @endif
            Pacchetto Essentials mensile </li>
        <li> 
            @if ($consumer['status'] == 1)
                <img src="{{ public_path('sx.png') }}" width="15" style="margin:3px;" alt="Box conferma">
            @else
                <img src="{{ public_path('square.png') }}" width="15" style="margin:3px;" alt="Box conferma">
            
            @endif
            Pacchetto Essentials annuale </li>
        <li> 
            @if ($consumer['status'] == 5)
                <img src="{{ public_path('sx.png') }}" width="15" style="margin:3px;" alt="Box conferma">
            @else
                <img src="{{ public_path('square.png') }}" width="15" style="margin:3px;" alt="Box conferma">
            
            @endif
            Pacchetto Work on mensile </li>
        <li> 
            @if ($consumer['status'] == 2)
                <img src="{{ public_path('sx.png') }}" width="15" style="margin:3px;" alt="Box conferma">
            @else
                <img src="{{ public_path('square.png') }}" width="15" style="margin:3px;" alt="Box conferma">
           
            @endif
            Pacchetto Work on annuale </li>
        <li> 
            @if ($consumer['status'] == 6)
                <img src="{{ public_path('sx.png') }}" width="15" style="margin:3px;" alt="Box conferma">
            @else
                <img src="{{ public_path('square.png') }}" width="15" style="margin:3px;" alt="Box conferma">
          
            @endif
            Pacchetto Boost up mensile </li>
        <li> 
            @if ($consumer['status'] == 3)
                <img src="{{ public_path('sx.png') }}" width="15" style="margin:3px;" alt="Box conferma">
            @else
                <img src="{{ public_path('square.png') }}" width="15" style="margin:3px;" alt="Box conferma">
           
            @endif
            Pacchetto Boost up annuale </li>
    </ul>

    <h3>Articolo 5</h3>
    <p class="sub">Spese</p>
    <p>Rimangono a totale carico del Cliente tutte le spese necessarie per l’esecuzione del presente contratto, salvo che non sia pattuito diversamente.</p>

    <h3>Articolo 6</h3>
    <p class="sub">Obblighi delle Parti</p>
    <p>
        Il Consulente si impegna a prestare, in proprio o tramite terzi, i Servizi con la dovuta diligenza e a
        regola d'arte, in conformità ad ogni legge e/o regolamento applicabile in materia.
    </p>
    <p>
        Il Consulente si impegna, altresì, ad assicurare o a fare in modo che operi, durante il periodo di
        vigenza del presente contratto, solo personale particolarmente preparato e dotato di adeguate
        conoscenze del settore tecnico d’interesse.
    </p>
    <p>
        Il Cliente si impegna a corrispondere al Consulente quanto pattuito sulla base del pacchetto
        prescelto ed a mettere a disposizione dello stesso, per tutta l’intera durata del Contratto, quanto
        necessario, o anche solamente utile, per consentire la prestazione dei Servizi, prestando la
        massima collaborazione e consentendo al Consulente di poter accedere a beni aziendali.
    </p>
    <p>
        Il Cliente si impegna, altresì, ad utilizzare il sito web, il dominio, la piattaforma e le relative
        funzionalità secondo le indicazioni ricevute dal Consulente, dovendosi, altrimenti, il Cliente
        medesimo, ritenere pienamente responsabile delle conseguenze di ogni intervento inadeguato.
    </p>
    <p>
        Il Cliente, con la sottoscrizione del presente contratto, dà atto che la policy privacy e i termini e le
        condizioni generali utilizzate dal Consulente possono essere generate da software compilatori
        automatici di documenti e si obbliga a nulla eccepire in ordine a tale modalità di generazione. 
    </p>
    <div style="page-break-before: always;"></div>
    <h3>Articolo 7</h3>
    <p class="sub">Risoluzione</p>
    <p>
        Il presente contratto potrà essere risolto per inadempimento, totale o parziale, di una delle Parti
        alle obbligazioni assunte mediante la stipula dello stesso.
    </p>
    <p>
        Si darà luogo alla risoluzione di diritto del contratto in caso di inadempimento degli obblighi assunti
        qualora entro 10 giorni dal preavviso ritualmente notificato mediante PEC e/o lettera
        raccomandata A/R ai sensi e per gli effetti dell'art. 1454 c.c. dovesse persistere il lamentato
        inadempimento.
    </p>
    <p>
        Con diritto di richiedere il risarcimento dei danni nei confronti della parte inadempiente.
    </p>
    <p>
        In ipotesi di risoluzione del presente contratto, il Cliente si obbliga, mediante l’aiuto del Consulente,
        a ripristinare la situazione e le funzionalità del sito web antecedenti all’inizio del presente incarico,
        rispettando, altresì, i diritti di proprietà e di proprietà intellettuale del relativo titolare.
    </p>

    <h3>Articolo 8</h3>
    <p class="sub">Esclusione di responsabilità</p>
    <p>
        Il Cliente utilizzerà il sito web, il dominio e la piattaforma web a proprio rischio, esonerando il
        Consulente, anche nei confronti di terzi, salvo il caso fortuito o la forza maggiore, da ogni
        responsabilità di natura civile, amministrativa o penale, nonché per danni indiretti, diritti, specifici,
        incidentali, cauzionali o consequenziali (a titolo esemplificativo e non esaustivo, danni conseguenti
        a: malfunzionamento, impossibilità di utilizzo o accesso ai servizi, perdita o corruzione di dati,
        perdita di profitti, diritti d'immagine, interruzioni dell'attività o simili), causati dall'utilizzo o
        dall'impossibilità di utilizzare il Servizio e basati su qualsiasi ipotesi di responsabilità.
    </p>

    <h3>Articolo 10</h3>
    <p class="sub">Modifiche al contratto</p>
    <p>
        Qualsiasi modifica del presente Contratto potrà farsi di comune accordo tra le Parti soltanto per
        atto scritto e da esse appositamente sottoscritto.
    </p>
    <p>
        Il presente contratto, comprese le Premesse e gli allegati, va inteso quale espressione integrale
        dell’accordo raggiunto tra le Parti con riferimento alla materia che ne costituisce oggetto e
        sostituisce ogni altro precedente accordo o intesa tra di esse intercorso riguardante il medesimo
        oggetto.
    </p>
    
    <h3>Articolo 11</h3>
    <p class="sub">Comunicazioni</p>
    <p>Tutte le comunicazioni relative al presente contratto dovranno essere effettuate ai recapiti indicati in epigrafe.</p>
    <div style="page-break-before: always;"></div>
    <h3>Articolo 12</h3>
    <p class="sub">Foro competente</p>
    <p>
        Qualunque controversia relativa e/o connessa al presente Contratto, ivi comprese quelle
        concernenti la validità, interpretazione, esecuzione o risoluzione del medesimo, sarà rimessa alla
        competenza esclusiva del Foro di Ancona, con espressa esclusione di ogni altra autorità giudiziaria
        eventualmente concorrente.
    </p>

    <div class="firma">
        <p>Luogo: _______________, data: {{ date('d/m/Y') }}</p>
        <p><strong>Il Consulente</strong></p>
        <img src="{{ public_path('firma_c.png') }}" width="150" alt="Firma del Consulente">
        <span>Firma</span>
        <p style="margin-bottom: 30px;"><strong>Il Cliente</strong></p>
        <span>Firma</span>
    </div>
    <p>
        Ai sensi e per gli effetti degli art. 1341, 2 comma, e 1342 c.c., le parti dichiarano di conoscere e di
        approvare specificamente i seguenti articoli: 2 (Durata), 3 (Corrispettivo), 5 (Spese) 6 (Obblighi
        delle Parti), 7 (Risoluzione), 8 (Esclusione di responsabilità), 9 (Riservatezza), 10 (Modifiche al
        contratto), 12 (Foro Competente).
    </p>
    <div class="firma">
        <p>Luogo: _______________, data: {{ date('d/m/Y') }}</p>
        <p><strong>Il Consulente</strong></p>
        <img src="{{ public_path('firma_c.png') }}" width="150" alt="Firma del Consulente">
        <span>Firma</span>
        <p style="margin-bottom: 30px;"><strong>Il Cliente</strong></p>
        <span>Firma</span>
    </div>
</body>
</html>
