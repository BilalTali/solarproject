const fs = require('fs');

const path = './src/pages/super-agent/SuperAgentPlaceholders.tsx';
const content = fs.readFileSync(path, 'utf8');

const leadDetailMatch = content.match(/export function SuperAgentLeadDetailPage\(\) \{[\s\S]*?(?=export function SuperAgentNotificationsPage)/);
const notificationsMatch = content.match(/export function SuperAgentNotificationsPage\(\) \{[\s\S]*?(?=export function SuperAgentProfilePage)/);
const profileMatch = content.match(/export function SuperAgentProfilePage\(\) \{[\s\S]*?(?=const InfoBlock)/);

// For simplicity, I'll extract everything above `export function SuperAgentLeadDetailPage` as imports.
const importsMatch = content.match(/^[\s\S]+?(?=export function SuperAgentLeadDetailPage)/);
const bottomHelpersMatch = content.match(/const InfoBlock = [\s\S]+$/);

if (importsMatch && leadDetailMatch && notificationsMatch && profileMatch) {
    fs.writeFileSync('./src/pages/super-agent/SuperAgentLeadDetailPage.tsx', importsMatch[0] + leadDetailMatch[0]);
    fs.writeFileSync('./src/pages/super-agent/SuperAgentNotificationsPage.tsx', importsMatch[0] + notificationsMatch[0]);
    fs.writeFileSync('./src/pages/super-agent/SuperAgentProfilePage.tsx', importsMatch[0] + profileMatch[0] + bottomHelpersMatch[0]);
    
    // Update App.tsx
    let appContent = fs.readFileSync('./src/App.tsx', 'utf8');
    appContent = appContent.replace(
        "import { SuperAgentLeadDetailPage, SuperAgentNotificationsPage, SuperAgentProfilePage } from '@/pages/super-agent/SuperAgentPlaceholders';",
        `import { SuperAgentLeadDetailPage } from '@/pages/super-agent/SuperAgentLeadDetailPage';\nimport { SuperAgentNotificationsPage } from '@/pages/super-agent/SuperAgentNotificationsPage';\nimport { SuperAgentProfilePage } from '@/pages/super-agent/SuperAgentProfilePage';`
    );
     appContent = appContent.replace(
        "import { SuperAgentNotificationsPage, SuperAgentProfilePage, SuperAgentLeadDetailPage } from '@/pages/super-agent/SuperAgentPlaceholders';",
        `import { SuperAgentLeadDetailPage } from '@/pages/super-agent/SuperAgentLeadDetailPage';\nimport { SuperAgentNotificationsPage } from '@/pages/super-agent/SuperAgentNotificationsPage';\nimport { SuperAgentProfilePage } from '@/pages/super-agent/SuperAgentProfilePage';`
    );
    // Actually just replace all kinds of imports from Placeholders
    appContent = appContent.replace(/import\s+\{[^}]+\}\s+from\s+['"]@\/pages\/super-agent\/SuperAgentPlaceholders['"];/g, 
        `import { SuperAgentLeadDetailPage } from '@/pages/super-agent/SuperAgentLeadDetailPage';\nimport { SuperAgentNotificationsPage } from '@/pages/super-agent/SuperAgentNotificationsPage';\nimport { SuperAgentProfilePage } from '@/pages/super-agent/SuperAgentProfilePage';`
    );
    fs.writeFileSync('./src/App.tsx', appContent);
    fs.unlinkSync(path);
    console.log("Split successful!");
} else {
    console.log("Failed to match code blocks");
}
