import BpmnFlowRepository from '../Repository/BpmnFlowRepository.js';
import BpmnIssueRepository from '../Repository/BpmnIssueRepository.js';
import BpmnLaneRepository from '../Repository/BpmnLaneRepository.js';
import BpmnNodeRepository from '../Repository/BpmnNodeRepository.js';
import BpmnPathRepository from '../Repository/BpmnPathRepository.js';
import BpmnProcessRepository from '../Repository/BpmnProcessRepository.js';
import BpmnTaskRightRepository from '../Repository/BpmnTaskRightRepository.js';

const generatedRepositories = [BpmnFlowRepository, BpmnIssueRepository, BpmnLaneRepository, BpmnNodeRepository, BpmnPathRepository, BpmnProcessRepository, BpmnTaskRightRepository];

export default generatedRepositories;
