import { Octokit } from '@octokit/rest';
import OpenAI from 'openai';
import { context } from '@actions/github';

async function analyzeChanges(openai, files, prInfo) {
    // Dividir arquivos em grupos menores para análise
    const FILES_PER_ANALYSIS = 3;
    const fileGroups = [];
    
    for (let i = 0; i < files.length; i += FILES_PER_ANALYSIS) {
        fileGroups.push(files.slice(i, i + FILES_PER_ANALYSIS));
    }

    let allAnalysis = [];

    for (let i = 0; i < fileGroups.length; i++) {
        const groupFiles = fileGroups[i];
        const analysisContext = `
            Pull Request #${prInfo.number}
            Title: ${prInfo.title}
            
            Analisando grupo de arquivos ${i + 1}/${fileGroups.length}:
            ${groupFiles.map(file => `
            File: ${file.filename}
            Status: ${file.status}
            Additions: ${file.additions}
            Deletions: ${file.deletions}
            Changes: ${file.changes}
            Patch:
            ${file.patch || 'No patch available'}
            `).join('\n')}
        `;

        console.log(`Analyzing file group ${i + 1}/${fileGroups.length}...`);

        const response = await openai.chat.completions.create({
            model: "gpt-4o",
            messages: [
                {
                    role: "system",
                    content: `Você é um revisor de código muito crítico e experiente que:
                    - Verifica boas práticas e indica SOLID
                    - Dá feedback construtivo
                    - Foca em problemas críticos
                    - Faz sugestões de melhorias quando relevante
                    - Verifica potenciais problemas de escalabilidade
                    - Analisa a clareza e manutenibilidade do código
                    - Identifica possíveis bugs ou edge cases
                    - Sugere testes quando apropriado
                    
                    Analise apenas os arquivos fornecidos neste grupo.
                    Seja conciso e direto, focando apenas nos pontos mais importantes.`
                },
                {
                    role: "user",
                    content: analysisContext
                }
            ],
            temperature: 0.8
        });

        allAnalysis.push(response.choices[0].message.content);
    }

    // Gerar resumo final
    if (allAnalysis.length > 1) {
        const summaryResponse = await openai.chat.completions.create({
            model: "gpt-4o",
            messages: [
                {
                    role: "system",
                    content: `Você é um revisor de código que deve consolidar múltiplas análises em um único resumo coerente.
                    Organize o feedback nas seguintes categorias:
                    1. Problemas Críticos (se houver)
                    2. Sugestões de Melhorias
                    3. Boas Práticas
                    4. Observações Gerais
                    
                    Seja conciso e evite repetições.`
                },
                {
                    role: "user",
                    content: `Consolide as seguintes análises em um único resumo:\n\n${allAnalysis.join('\n\n')}`
                }
            ],
            temperature: 0.7
        });
        
        return summaryResponse.choices[0].message.content;
    }

    return allAnalysis[0];
}

async function run() {
    try {
        const openai = new OpenAI({
            apiKey: process.env.OPENAI_API_KEY
        });

        const octokit = new Octokit({
            auth: process.env.GITHUB_TOKEN
        });

        const { pull_request } = context.payload;
        
        if (!pull_request) {
            console.log('No pull request found in context');
            return;
        }

        console.log(`Processing PR #${pull_request.number}`);

        const { data: files } = await octokit.pulls.listFiles({
            owner: context.repo.owner,
            repo: context.repo.repo,
            pull_number: pull_request.number
        });

        const reviewComment = await analyzeChanges(openai, files, pull_request);

        console.log('Adding review comment to PR...');

        await octokit.issues.createComment({
            owner: context.repo.owner,
            repo: context.repo.repo,
            issue_number: pull_request.number,
            body: `## 🔍 Análise 

${reviewComment}

---
*Análise gerada automaticamente utilizando IA*`
        });

        console.log('Code review completed successfully');

    } catch (error) {
        console.error('Error:', error.message);
        process.exit(1);
    }
}

run();
