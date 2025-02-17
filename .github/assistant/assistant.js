import { Octokit } from '@octokit/rest';
import OpenAI from 'openai';
import { context } from '@actions/github';

async function run() {
    try {
        // Initialize clients
        const openai = new OpenAI({
            apiKey: process.env.OPENAI_API_KEY
        });

        const octokit = new Octokit({
            auth: process.env.GITHUB_TOKEN
        });

        // Get PR details from context
        const { pull_request } = context.payload;
        
        if (!pull_request) {
            console.log('No pull request found in context');
            return;
        }

        console.log(`Processing PR #${pull_request.number}`);

        // Get PR files
        const { data: files } = await octokit.pulls.listFiles({
            owner: context.repo.owner,
            repo: context.repo.repo,
            pull_number: pull_request.number
        });

        // Prepare context for analysis
        const analysisContext = `
            Pull Request #${pull_request.number}
            Title: ${pull_request.title}
            Description: ${pull_request.body || 'No description provided'}
            
            Changes:
            ${files.map(file => `
            File: ${file.filename}
            Status: ${file.status}
            Additions: ${file.additions}
            Deletions: ${file.deletions}
            Changes: ${file.changes}
            Patch:
            ${file.patch || 'No patch available'}
            `).join('\n')}
        `;

        console.log('Analyzing PR with OpenAI...');

        // Analyze with GPT
        const response = await openai.chat.completions.create({
            model: "gpt-4",
            messages: [
                {
                    role: "system",
                    content: `Você é um revisor de código muito crítico e experiente que:
                    - Procura problemas de segurança
                    - Sugere melhorias de performance
                    - Verifica boas práticas
                    - Dá feedback construtivo
                    - Foca em problemas críticos
                    - Faz sugestões de melhorias quando relevante
                    - Verifica potenciais problemas de escalabilidade
                    - Analisa a clareza e manutenibilidade do código
                    - Identifica possíveis bugs ou edge cases
                    - Sugere testes quando apropriado
                    - Se for logica de programação analise se esta dentro dos padrões do SOLID.
                    
                    Forneça o feedback em português, organizando por categorias:
                    1. Problemas Críticos (se houver)
                    2. Sugestões de Melhorias
                    3. Boas Práticas
                    4. Observações Gerais`
                },
                {
                    role: "user",
                    content: analysisContext
                }
            ],
            temperature: 0.8
        });

        const reviewComment = response.choices[0].message.content;

        console.log('Adding review comment to PR...');

        // Add comment to PR
        await octokit.issues.createComment({
            owner: context.repo.owner,
            repo: context.repo.repo,
            issue_number: pull_request.number,
            body: `## 🔍 Análise 

${reviewComment}
`
        });

        console.log('Code review completed successfully');

    } catch (error) {
        console.error('Error:', error.message);
        process.exit(1);
    }
}

// Execute the action
run();
